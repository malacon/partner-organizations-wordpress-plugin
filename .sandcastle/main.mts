// Parallel Sandcastle orchestration for AFK GitHub issues.
//
// Usage:
//   npm run sandcastle
//
// The loop plans runnable issues, executes unblocked issues in parallel, merges
// completed branches, and appends AI usage trace entries to AI_USAGE_LOG.md so
// the final README can truthfully summarize AI-assisted development.

import { appendFileSync, existsSync, mkdirSync, readFileSync, rmSync, readdirSync } from "node:fs";
import { execSync } from "node:child_process";
import { resolve } from "node:path";
import * as sandcastle from "@ai-hero/sandcastle";
import { docker, type DockerOptions } from "@ai-hero/sandcastle/sandboxes/docker";

const PROJECT_ROOT = resolve(import.meta.dirname, "..");
const AI_USAGE_LOG = resolve(PROJECT_ROOT, "AI_USAGE_LOG.md");
const MAX_ITERATIONS = 10;
const MODEL = "openai-codex/gpt-5.5";
const IDLE_TIMEOUT_SECONDS = 1800;

function loadEnvFile(path: string): void {
  if (!existsSync(path)) return;

  for (const line of readFileSync(path, "utf-8").split("\n")) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith("#")) continue;

    const eq = trimmed.indexOf("=");
    if (eq > 0) {
      process.env[trimmed.slice(0, eq)] = trimmed.slice(eq + 1);
    }
  }
}

loadEnvFile(resolve(import.meta.dirname, ".env"));
loadEnvFile(resolve(PROJECT_ROOT, ".env"));

if (!process.env.GH_TOKEN) {
  console.error("GH_TOKEN not found. Add it to .sandcastle/.env.");
  process.exit(1);
}

function appendAiUsage(entry: string): void {
  const timestamp = new Date().toISOString();
  if (!existsSync(AI_USAGE_LOG)) {
    appendFileSync(
      AI_USAGE_LOG,
      "# AI Usage Log\n\nRaw project log of Sandcastle-assisted development. Summarize this in README.md before submission.\n\n",
    );
  }
  appendFileSync(AI_USAGE_LOG, `## ${timestamp}\n\n${entry.trim()}\n\n`);
}

const hooks = {
  sandbox: { onSandboxReady: [{ command: "npm install" }] },
};

const ghConfigDir = process.env.HOME + "/.config/gh";
const piConfigDir = resolve(import.meta.dirname, "pi-agent");

const dockerOpts = {
  imageName: "sandcastle:partner-organizations",
  mounts: [
    { hostPath: ghConfigDir, sandboxPath: "/home/agent/.config/gh" },
    { hostPath: piConfigDir, sandboxPath: "/home/agent/.pi/agent" },
  ],
  env: {
    GH_TOKEN: process.env.GH_TOKEN,
    ANTHROPIC_BASE_URL: process.env.ANTHROPIC_BASE_URL,
    ANTHROPIC_AUTH_TOKEN: process.env.ANTHROPIC_AUTH_TOKEN,
    ZAI_API_KEY: process.env.ZAI_API_KEY,
  },
};

for (let iteration = 1; iteration <= MAX_ITERATIONS; iteration++) {
  console.log(`\n=== Iteration ${iteration}/${MAX_ITERATIONS} ===\n`);
  appendAiUsage(`Sandcastle iteration ${iteration} started. Planner model: ${MODEL}.`);

  const plan = await sandcastle.run({
    hooks,
    sandbox: docker(dockerOpts as unknown as DockerOptions),
    name: "planner",
    maxIterations: 1,
    agent: sandcastle.pi(MODEL),
    promptFile: "./.sandcastle/plan-prompt.md",
  });

  const planMatch = plan.stdout.match(/<plan>([\s\S]*?)<\/plan>/);
  if (!planMatch) {
    appendAiUsage(`Planner failed to emit <plan> on iteration ${iteration}.`);
    throw new Error("Planning agent did not produce a <plan> tag.\n\n" + plan.stdout);
  }

  const { issues } = JSON.parse(planMatch[1]!) as {
    issues: { id: string; title: string; branch: string }[];
  };

  appendAiUsage(
    `Planner selected ${issues.length} issue(s) for iteration ${iteration}:\n` +
      (issues.length
        ? issues.map((issue) => `- #${issue.id}: ${issue.title} (${issue.branch})`).join("\n")
        : "- None"),
  );

  if (issues.length === 0) {
    console.log("No unblocked issues to work on. Exiting.");
    break;
  }

  const settled = await Promise.allSettled(
    issues.map((issue) =>
      sandcastle.run({
        hooks,
        idleTimeoutSeconds: IDLE_TIMEOUT_SECONDS,
        sandbox: docker(dockerOpts as unknown as DockerOptions),
        branchStrategy: { type: "branch", branch: issue.branch },
        name: `issue-${issue.id}-implementer`,
        maxIterations: 100,
        agent: sandcastle.pi(MODEL),
        promptFile: "./.sandcastle/implement-prompt.md",
        promptArgs: {
          TASK_ID: issue.id,
          ISSUE_TITLE: issue.title,
          BRANCH: issue.branch,
        },
      }),
    ),
  );

  for (const [i, outcome] of settled.entries()) {
    const issue = issues[i]!;
    if (outcome.status === "rejected") {
      console.error(`  ✗ ${issue.id} (${issue.branch}) failed: ${outcome.reason}`);
      appendAiUsage(`Implementer failed for #${issue.id} (${issue.branch}): ${String(outcome.reason)}`);
    } else {
      appendAiUsage(
        `Implementer finished for #${issue.id} (${issue.branch}). Commits produced: ${outcome.value.commits.length}. Model: ${MODEL}.`,
      );
    }
  }

  const completedIssues = settled
    .map((outcome, i) => ({ outcome, issue: issues[i]! }))
    .filter(
      (
        entry,
      ): entry is {
        outcome: PromiseFulfilledResult<Awaited<ReturnType<typeof sandcastle.run>>>;
        issue: (typeof issues)[number];
      } => entry.outcome.status === "fulfilled" && entry.outcome.value.commits.length > 0,
    )
    .map((entry) => entry.issue);

  const completedBranches = completedIssues.map((issue) => issue.branch);

  if (completedBranches.length === 0) {
    appendAiUsage(`Iteration ${iteration}: no branches produced commits; nothing to merge.`);

    try {
      execSync("git worktree prune", { cwd: PROJECT_ROOT, stdio: "pipe" });
      const worktreesDir = resolve(PROJECT_ROOT, ".sandcastle/worktrees");
      if (existsSync(worktreesDir)) {
        for (const entry of readdirSync(worktreesDir, { withFileTypes: true })) {
          if (entry.isDirectory()) {
            rmSync(resolve(worktreesDir, entry.name), { recursive: true, force: true });
          }
        }
      }
    } catch (err) {
      console.warn("Failed to prune stale worktrees:", err);
    }

    continue;
  }

  appendAiUsage(
    `Merger starting for iteration ${iteration}. Model: ${MODEL}. Branches:\n` +
      completedBranches.map((branch) => `- ${branch}`).join("\n"),
  );

  await sandcastle.run({
    hooks,
    sandbox: docker(dockerOpts as unknown as DockerOptions),
    name: "merger",
    maxIterations: 1,
    agent: sandcastle.pi(MODEL),
    promptFile: "./.sandcastle/merge-prompt.md",
    promptArgs: {
      BRANCHES: completedBranches.map((branch) => `- ${branch}`).join("\n"),
      ISSUES: completedIssues.map((issue) => `- ${issue.id}: ${issue.title}`).join("\n"),
    },
  });

  appendAiUsage(`Merger completed for iteration ${iteration}.`);
  console.log("\nBranches merged.");
}

appendAiUsage("Sandcastle orchestration finished.");
console.log("\nAll done.");
