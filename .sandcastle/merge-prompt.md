# TASK

Merge the following branches into the current branch:

{{BRANCHES}}

For each branch:

1. Run `git merge <branch> --no-edit`
2. If there are merge conflicts, resolve them intelligently by reading both sides and choosing the correct resolution
3. Run the strongest available verification after each successful merge:
   - Preferred: `docker compose --profile test run --rm plugin-tests`
   - If unavailable because the test runner has not been built yet, run applicable lighter checks and document the limitation.
4. If tests fail, fix the issues before proceeding to the next branch

# AI USAGE LOGGING

Ensure merged branches retain their per-issue files under `docs/ai-usage/`.

Append a concise merger entry to `AI_USAGE_LOG.md` describing:

- Branches merged
- Model/tool used: Sandcastle with Pi using GPT-5.5
- Verification run
- Conflicts or corrections made

Do not include secrets.

# CLOSE ISSUES

For each branch that was merged, close its issue using the issue IDs listed below:

{{ISSUES}}

Use this command for each merged issue, replacing `<issue-id>` with the numeric issue ID:

`gh issue close <issue-id> --comment "Completed by Sandcastle"`

Once you've merged everything you can, output <promise>COMPLETE</promise>.
