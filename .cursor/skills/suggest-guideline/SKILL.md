---
name: suggest-guideline
description: Create a PR to add or update a coding guideline rule. Use when the user says "fix this in my guidelines", "add this to guidelines", or invokes /suggest-guideline.
user-invocable: true
disable-model-invocation: true
boost: false
allowed-tools:
  - "Bash(gh api:*)"
  - "Bash(gh pr:*)"
  - "Bash(which gh:*)"
  - "Bash(echo *|base64:*)"
  - "Bash(printf *|base64:*)"
---

# Suggest guideline

You are creating a PR to add or update a coding guideline in the `ralphjsmit/guidelines` private GitHub repository.

## Step 1: understand the context

Analyze the current conversation to understand what guideline rule the user wants to add or update. If the intent is ambiguous, ask for clarification before proceeding.

## Step 2: determine the target file

Choose the correct file based on the domain of the rule:

| Domain | File path |
|---|---|
| General coding standards (branch naming, linting, headings, commit messages) | `resources/guidelines/01-coding-standards.md` |
| PHP language (types, control flow, classes, docblocks, enums) | `resources/guidelines/02-php-standards.md` |
| Laravel conventions (routes, Eloquent, strings, Carbon, config, migrations, Blade) | `resources/guidelines/03-laravel-conventions.md` |
| Pest testing | `resources/skills/test-writer/SKILL.md` |
| Filament PHP | `resources/skills/filament/SKILL.md` |

If no file clearly fits, ask the user which file to target.

## Step 3: formulate the rule

Write the rule in the same style as existing rules in the target file:

- Use imperative bullet points (e.g. "Always use...", "NEVER use...", "Prefer...")
- Include Good/Bad code examples where helpful, using fenced PHP code blocks
- Match the heading level and section structure of the target file
- Keep it concise — one rule per bullet point

## Step 4: create the PR via `gh api`

Run the following commands sequentially. The repository is `ralphjsmit/guidelines`.

### 4a. Verify `gh` is available

```bash
which gh
```

If `gh` is not found, fall back to `curl` with `$GITHUB_TOKEN` (see step 6).

### 4b. Get the SHA of `main`

```bash
gh api repos/ralphjsmit/guidelines/git/ref/heads/main --jq '.object.sha'
```

### 4c. Create the branch

Generate a branch name: `rjs/guideline-<short-kebab-description>` (e.g. `rjs/guideline-no-empty-function`).

```bash
gh api repos/ralphjsmit/guidelines/git/refs -f ref="refs/heads/<branch-name>" -f sha="<main-sha>"
```

### 4d. Get the current file content

```bash
gh api "repos/ralphjsmit/guidelines/contents/<file-path>?ref=<branch-name>"
```

Extract the `sha` (blob SHA) and `content` (base64-encoded) fields from the response.

### 4e. Decode, modify, and re-encode

Decode the base64 content, apply the edit (append the new rule in the appropriate section, or modify existing text), and re-encode:

```bash
echo '<base64-content>' | base64 --decode > /tmp/guideline-edit.md
```

Edit the file as needed, then:

```bash
base64 -i /tmp/guideline-edit.md
```

### 4f. Commit the updated file

```bash
gh api repos/ralphjsmit/guidelines/contents/<file-path> \
  -X PUT \
  -f message="docs: <short description>" \
  -f content="<new-base64-content>" \
  -f sha="<blob-sha>" \
  -f branch="<branch-name>"
```

### 4g. Create the pull request

Use the **Conventional Commits** convention for the PR title. Choose the appropriate type based on the change:

- `docs:` — adding or updating a guideline rule (most common)
- `fix:` — correcting an incorrect or outdated rule
- `feat:` — adding a new guideline category or section

The description should be lowercase and concise (e.g. `docs: prefer multi-line docblocks`, `fix: correct enum naming convention`).

```bash
gh api repos/ralphjsmit/guidelines/pulls \
  -f title="<type>: <short description>" \
  -f body="<description of the rule and why it was added>" \
  -f head="<branch-name>" \
  -f base="main"
```

## Step 5: report back

Show the user:
- The PR URL
- A summary of the rule that was added/updated
- Which file was modified

## Step 6: fallback (no `gh` CLI)

If `gh` is not available, use `curl` with the `GITHUB_TOKEN` environment variable:

```bash
curl -s -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github+json" \
  https://api.github.com/repos/ralphjsmit/guidelines/...
```

Follow the same steps as above, replacing `gh api` calls with equivalent `curl` commands.
