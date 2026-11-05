---
name: mayhen-project-architecture
description: Architecture conventions, folder structure, layer responsibilities, and boundaries for May Hen (mayhen.vn).
---

# mayhen-project-architecture

## Applicable Paths
`app/, routes/, config/, bootstrap/`

## Purpose
Define system architecture, directory conventions, and layer boundaries for May Hen.

## Mandatory Rules
- Strictly follow Laravel 13 directory conventions.
- Do not mix Client, Admin, and Partner logic.
- Controllers must be thin; business logic resides in Services or Domain Actions.
- Do not install unauthorized composer packages.
- Preserve existing code; do not delete files without approval.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
