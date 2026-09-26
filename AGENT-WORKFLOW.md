# Agent Workflow

1. User and Sol discuss the requirement.
2. Sol defines a bounded task and acceptance criteria.
3. `main` contains accepted state.
4. Luna syncs `main`.
5. Luna creates a task branch named `luna/STORE-XXX-short-description`.
6. Luna implements.
7. Luna runs automated checks.
8. Luna performs browser UAT where applicable.
9. Luna updates context files.
10. Luna pushes the task branch.
11. Luna provides a handoff and commit SHA.
12. Sol inspects the GitHub branch and diff.
13. The work is fixed or accepted.
14. The accepted branch is merged to `main`.
15. `main` becomes the new accepted state.

The initial empty-repository bootstrap is the exception and may be committed directly to `main`.

## Anti-circling rule

If two materially different implementation approaches fail, stop and document the evidence, hypotheses, attempted approaches, and the decision or information needed. Do not repeatedly try random variants.

