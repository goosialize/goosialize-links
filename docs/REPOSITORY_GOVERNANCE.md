# Repository Governance

## 1. Repository Purpose

This repository is the source of truth for the standalone Goosialize Links
plugin for Grav CMS.

The repository must not contain:

- a complete Grav installation;
- runtime website data;
- production credentials;
- customer data;
- Goosialize Leads source code;
- private GoosBoard source code;
- generated release archives.

## 2. Protected Main Branch

The `main` branch represents the latest reviewed and approved project state.

Implementation work must not begin directly on `main`.

Every change begins from:

1. an explicitly verified base branch;
2. an explicitly verified base commit;
3. a clean working tree;
4. a new task-specific branch.

## 3. Branch Naming

Approved branch prefixes are:

- `docs/` for documentation and contracts;
- `feature/` for new functionality;
- `fix/` for defect corrections;
- `test/` for isolated testing work;
- `release/` for release preparation.

Examples:

- `docs/project-governance`
- `feature/plugin-skeleton`
- `feature/profile-storage`
- `feature/admin2-profile-settings`
- `fix/qr-safe-redirect`
- `release/1.0.0`

Branches must describe one coherent change.

## 4. Base Commit Verification

Every implementation block must declare its expected base commit.

Before changing files, the workflow must verify:

- repository path;
- current branch;
- exact HEAD;
- clean working tree;
- expected prerequisite files;
- absence of unapproved files.

A checkpoint must stop when any verification fails.

## 5. Approved File Inventory

Every checkpoint must declare the exact files or path prefixes it may change.

The implementation must not silently:

- expand the file inventory;
- modify unrelated documentation;
- rewrite formatting in unrelated files;
- introduce generated dependencies;
- change another project or repository.

When the required implementation exceeds the approved inventory, the contract
must be amended before continuing.

## 6. Review Before Commit

Changes remain uncommitted until their complete diff has been reviewed.

Before commit, the workflow must verify:

- exact changed-file set;
- syntax and format checks;
- content or behavior checks;
- no unexpected staged files;
- no secrets;
- no unrelated modifications.

Commit creation requires explicit review approval.

## 7. Commit Rules

Commits must be:

- small enough to review;
- limited to one coherent purpose;
- reproducible from the documented checkpoint;
- free of generated runtime data;
- free of unrelated formatting changes.

Preferred commit prefixes include:

- `docs:`
- `feat:`
- `fix:`
- `test:`
- `refactor:`
- `chore:`
- `release:`

## 8. Merge Rules

Approved branches should normally be integrated into `main` using a
fast-forward merge after all branch checks pass.

The merge workflow must verify:

- the expected `main` HEAD;
- the expected feature branch HEAD;
- clean worktrees;
- the exact commit range;
- the exact cumulative changed-file inventory.

History must not be rewritten after a reviewed commit has been used as a
checkpoint unless a documented recovery procedure explicitly requires it.

## 9. Repository-Local Git Identity

The repository uses a repository-local Git identity:

- Name: `Constantinos Chinopoulos`
- Email: `agency@goosialize.com`

The project workflow must not require changing the user's global Git identity.

## 10. Source and Runtime Separation

The repository contains plugin source files.

Runtime storage belongs to the Grav installation and must not be committed to
this repository.

Expected runtime locations may include:

- `user/config/plugins/goosialize-links.yaml`
- `user/data/goosialize-links/`

Development wiring between this repository and a Grav installation must use a
controlled, documented mechanism.

The same source file must not be edited independently in both the repository
and the Grav installation.

## 11. External Project Boundaries

This repository must not directly modify:

- `goosialize-leads-dev`;
- `goosialize-v2-staging`;
- STAYTUNED or GoosBoard repositories;
- unrelated Grav plugins or themes.

Any required cross-project contract change must be handled as a separate,
reviewed task in the affected repository.

## 12. Secrets and Personal Data

The repository must never contain:

- API keys;
- SMTP credentials;
- SendPulse credentials;
- access tokens;
- private keys;
- customer submissions;
- real analytics visitor data;
- production database copies.

Examples and tests must use synthetic data.

## 13. Dependency Policy

Dependencies must be:

- necessary for the locked product scope;
- compatible with the target Grav and PHP runtime;
- pinned or constrained deliberately;
- reviewed for licensing and maintenance status;
- added only in the checkpoint that requires them.

Vendor directories and generated dependency trees are not committed unless a
future release contract explicitly requires packaged vendoring.

## 14. Contract Precedence

Implementation must comply with:

1. `AGENTS.md`;
2. `docs/FREE_PRODUCT_CONTRACT.md`;
3. `docs/ARCHITECTURE_PRINCIPLES.md`;
4. `docs/PROJECT_SCOPE.md`;
5. `docs/IMPLEMENTATION_MANIFEST.md`;
6. the current checkpoint's approved file inventory.

If two contracts contradict each other, implementation stops until the
contradiction is corrected explicitly.

## 15. Recovery Rule

When a checkpoint fails:

- do not guess;
- do not create a compensating commit automatically;
- inspect the actual Git and filesystem state;
- identify whether content changed;
- preserve valid staged or unstaged work;
- issue a narrowly scoped recovery checkpoint.

## 16. Release Packaging

Release archives must be generated from a clean reviewed commit.

A release archive must not contain:

- `.git`;
- development-only files not approved for distribution;
- local configuration;
- test output;
- runtime data;
- credentials;
- unrelated documentation artifacts.

Release packaging rules will be locked during the release-readiness phase.
