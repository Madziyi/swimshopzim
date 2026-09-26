# Agent Responsibilities

## Sol

Sol is the product and technical lead responsible for product architecture, UX architecture, WooCommerce architecture, design direction, requirements, milestone planning, acceptance criteria, research, code review, UX review and deployment decisions.

## Luna

Luna is responsible for local implementation, repository work, running the Local WP development environment, browser testing, responsive visual UAT, debugging, automated checks, implementation documentation and preparing branches/commits for Sol review.

## One-writer rule

When Luna is actively executing a task, Luna temporarily owns the mutable task-state context files. Sol must not silently change `NOW.md` underneath an active Luna implementation. Luna completes implementation, verification and handoff first; Sol then resynchronizes and reviews.

GitHub is the durable shared memory. Repository state, not chat memory, is authoritative.

