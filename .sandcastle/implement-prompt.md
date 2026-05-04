# TASK

Fix issue {{TASK_ID}}: {{ISSUE_TITLE}}

Pull in the issue using `gh issue view {{TASK_ID}}`. If it has a parent PRD, pull that in too.

Only work on the issue specified.

Work on branch {{BRANCH}}. Make commits and run tests.

# CONTEXT

Here are the last 10 commits:

<recent-commits>

!`git log -n 10 --format="%H%n%ad%n%B---" --date=short`

</recent-commits>

Read these project files before changing code:

- `CONTEXT.md`
- `docs/adr/0001-public-partner-api-design.md`
- `README.md`

# EXPLORATION

Explore the repo and fill your context window with relevant information that will allow you to complete the task.

Pay extra attention to test files that touch the relevant parts of the code.

# EXECUTION

If applicable, use RGR to complete the task.

1. RED: write one test
2. GREEN: write the implementation to pass that test
3. REPEAT until done
4. REFACTOR the code

Follow the project decisions already made:

- Domain term: Partner Organization.
- Use idiomatic WordPress APIs.
- Prefer responsibility-focused PHP classes under the `PartnerOrganizations\\` namespace.
- Keep Composer production-packaging concerns inside `partner-organizations/`.
- Escape output, sanitize input, verify nonces, and check capabilities.

# AI USAGE LOGGING

Before committing, create or update a per-issue AI usage note at:

`docs/ai-usage/sandcastle-issue-{{TASK_ID}}.md`

Include:

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped on this issue.
- What you reviewed, corrected, or rejected.
- How you verified correctness.
- Any AI limitation/mistake or uncertainty encountered.
- Security/maintainability checks performed.

Keep this factual and concise. Do not include secrets.

# FEEDBACK LOOPS

Before committing, run the strongest available verification for the current state.

Preferred once available:

```bash
docker compose --profile test run --rm plugin-tests
```

If the Dockerized test runner does not exist yet, run applicable lighter checks such as:

```bash
php -l partner-organizations/partner-organizations.php
npm test
```

If a command cannot run because prerequisite infrastructure is not part of this issue yet, note that in the per-issue AI usage file and issue comment if the task is incomplete.

# COMMIT

Make a git commit. The commit message must:

1. Start with `RALPH:` prefix
2. Include task completed and issue reference
3. Key decisions made
4. Files changed
5. Blockers or notes for next iteration

Keep it concise.

# THE ISSUE

If the task is not complete, leave a comment on the issue with what was done.

Do not close the issue - this will be done later.

Once complete, output <promise>COMPLETE</promise>.

# FINAL RULES

ONLY WORK ON A SINGLE TASK.
