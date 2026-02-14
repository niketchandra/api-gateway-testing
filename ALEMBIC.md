# Alembic guide

Alembic manages SQLAlchemy migrations so schema changes are tracked and applied safely.

## How it is wired
- alembic.ini holds the default SQLAlchemy URL.
- alembic/env.py loads Base.metadata and can override the URL using DATABASE_URL.
- The migration scripts live in alembic/versions.

## Current migration
- 20260214_add_password_hash.py adds the password_hash column to users.

## Common commands
Create a migration (autogenerate):

```bash
alembic revision --autogenerate -m "describe change"
```

Apply migrations:

```bash
alembic upgrade head
```

## Docker usage
If the DB already exists, run migrations in the api container:

```bash
docker compose run --rm api alembic upgrade head
```

## Troubleshooting
- "Unknown column" errors mean the migration was not applied.
- If the local shell cannot find alembic, run it inside the container.
