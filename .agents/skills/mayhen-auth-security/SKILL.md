---
name: mayhen-auth-security
description: Authentication, role-based authorization, XSS/CSRF defense, and personal data privacy (Law 91/2025/QH15).
---

# mayhen-auth-security

## Applicable Paths
`app/Http/Middleware/, app/Policies/, app/Models/User.php`

## Purpose
Protect user privacy and application security.

## Mandatory Rules
- Strict compliance with Vietnamese personal data protection laws.
- Personal invitation links must use secure, unguessable tokens.
- Never expose guest list data of one wedding to another user or guest.
- Strict file upload validation: mime types, dimensions, storage isolation.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
