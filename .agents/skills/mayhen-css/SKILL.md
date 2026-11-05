---
name: mayhen-css
description: Strict Vanilla CSS conventions, mandatory mayhen_ class prefixing, tokens, zero CSS framework.
---

# mayhen-css

## Applicable Paths
`public/assets/client/css/, public/assets/admin/css/`

## Purpose
Enforce pure Vanilla CSS architecture without Tailwind or Bootstrap, with mandatory prefixed class names.

## Mandatory Rules
- ZERO CSS FRAMEWORKS: No Tailwind, Bootstrap, Bulma, Foundation.
- MANDATORY PREFIX: Every class MUST start with mayhen_[scope]_[component]_[element] (e.g., mayhen_home_hero, mayhen_client_button).
- Never use generic class names like .container, .row, .card, .btn.
- CSS files structured into: common/tokens.css, common/base.css, components/, pages/.
- No !important unless strictly overriding isolated user agents.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
