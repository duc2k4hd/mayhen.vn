---
name: mayhen-blade
description: Blade templating rules: one page one file, reusable layouts and components, XSS escaping.
---

# mayhen-blade

## Applicable Paths
`resources/views/`

## Purpose
Enforce clean Blade templating without inline CSS/JS.

## Mandatory Rules
- One independent Blade file per page (e.g. client/pages/home.blade.php).
- Reusable components in client/components/ or admin/components/.
- No inline <style> or inline <script> tags inside Blade templates.
- All dynamic user content MUST be properly escaped with {{ }}.
- Semantic HTML5 tags (header, nav, main, section, footer, article).

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
