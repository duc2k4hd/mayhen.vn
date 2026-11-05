---
name: mayhen-deployment
description: Production deployment workflows, environment configuration, caching, backups, rollback.
---

# mayhen-deployment

## Applicable Paths
`config/, .env.example`

## Purpose
Guide zero-downtime deployments and safe server operations.

## Mandatory Rules
- Run config:cache, route:cache, view:cache in production.
- Database migrations run with --force only in automated deployment scripts.
- Automated database backups before schema updates.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
