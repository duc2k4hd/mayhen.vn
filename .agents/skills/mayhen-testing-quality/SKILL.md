---
name: mayhen-testing-quality
description: Pest testing strategy, feature tests, browser verification, cleanup of temporary test files.
---

# mayhen-testing-quality

## Applicable Paths
`tests/`

## Purpose
Ensure rock-solid reliability and test coverage.

## Mandatory Rules
- Use Pest (vendor/bin/pest or php artisan test).
- Write Feature tests for critical user journeys (Editor, RSVP, Checkout).
- ALWAYS clean up temporary test files or artifacts created during testing.
- Never delete existing test suites.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
