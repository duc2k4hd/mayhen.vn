---
name: mayhen-laravel-backend
description: Laravel best practices, Eloquent queries, Form Requests, Policies, Services, and transactions.
---

# mayhen-laravel-backend

## Applicable Paths
`app/Http/Controllers/, app/Services/, app/Http/Requests/`

## Purpose
Ensure high-quality, maintainable PHP and Laravel backend code.

## Mandatory Rules
- Follow PHP 8.3+ typing (explicit return types, property promotion).
- Validate every incoming request with dedicated FormRequest classes.
- Prevent N+1 queries using eager loading (with, load).
- Wrap multi-table operations in DB::transaction().

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
