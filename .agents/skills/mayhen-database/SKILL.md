---
name: mayhen-database
description: Database schema design, relationships, foreign keys, indexes, migrations review.
---

# mayhen-database

## Applicable Paths
`database/migrations/, app/Models/, docs/database/`

## Purpose
Maintain a clean, scalable database schema.

## Mandatory Rules
- Never create or delete tables/columns arbitrarily.
- Always review docs/database/database-design.md before generating migrations.
- Proper indexing on foreign keys and lookup columns (slug, user_id, wedding_id, status).
- Use soft deletes only where business audit requires it.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
