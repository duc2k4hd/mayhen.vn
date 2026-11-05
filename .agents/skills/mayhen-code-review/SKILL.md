---
name: mayhen-code-review
description: Self-review checklist: CSS prefixes, vanilla JS verification, XSS checks, Pint formatting.
---

# mayhen-code-review

## Applicable Paths
`app/, resources/, public/`

## Purpose
Enforce strict quality control before finalizing any task.

## Mandatory Rules
- Format all modified PHP files with vendor/bin/pint --dirty --format agent.
- Verify all CSS classes have mayhen_ prefix.
- Confirm zero external CSS/JS libraries were added.
- Confirm all temporary test files have been deleted.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
