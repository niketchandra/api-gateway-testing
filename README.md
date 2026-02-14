# FastAPI User CRUD with MySQL and Kong

This project provides a complete CRUD API for a User resource using FastAPI and MySQL, exposed through Kong API Gateway.

## Docs
- FastAPI design and code tour: [FASTAPI.md](FASTAPI.md)
- Kong config and routing: [KONG.md](KONG.md)
- Alembic migrations: [ALEMBIC.md](ALEMBIC.md)

## Docker Compose (API + MySQL + Kong)
1. Ensure SECRET_KEY is set in docker-compose.yml (replace "change-me").
2. Start everything:

```bash
docker compose -f docker-compose.yml -f docker-compose-kong.yml up -d
```

3. Endpoints:
- API (direct): http://localhost:8000
- Kong proxy: http://localhost:8002
- Kong admin: http://localhost:8001
- phpMyAdmin: http://localhost:8080 (user root, password empty)

## Kong API description
Kong runs in DB-less mode and loads kong/kong.yml at startup. The config defines:
- A users service with a /users route for all CRUD methods.
- An auth service with /auth/login and /auth/logout.
- A rate-limiting plugin on the users service.

Details: [KONG.md](KONG.md)

## CRUD commands (via Kong)
These examples use a default test user. If it does not exist, create it first.

Default test credentials:
- Email: user001@example.com
- Password: Secret123!

Create user:

```bash
curl -X POST http://localhost:8002/users \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"User001\",\"email\":\"user001@example.com\",\"password\":\"Secret123!\"}"
```

List users:

```bash
curl http://localhost:8002/users
```

Get user:

```bash
curl http://localhost:8002/users/1
```

Update user:

```bash
curl -X PUT http://localhost:8002/users/1 \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"User001 Updated\"}"
```

Delete user:

```bash
curl -X DELETE http://localhost:8002/users/1
```

## Login and logout (via Kong)
Login:

```bash
curl -X POST http://localhost:8002/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"user001@example.com\",\"password\":\"Secret123!\"}"
```

Retrieve token from login response:

```bash
curl -s -X POST http://localhost:8002/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"user001@example.com\",\"password\":\"Secret123!\"}" | python -c "import sys, json; print(json.load(sys.stdin)['access_token'])"
```

Logout:

```bash
curl -X POST http://localhost:8002/auth/logout \
  -H "Authorization: Bearer <token>"
```

## Alembic
- The latest migration adds the password_hash column.
- Apply migrations in the container when the DB already exists:

```bash
docker compose run --rm api alembic upgrade head
```

Details: [ALEMBIC.md](ALEMBIC.md)

## Troubleshooting
- Kong says "no Route matched": restart Kong after editing kong/kong.yml.
  - docker compose -f docker-compose.yml -f docker-compose-kong.yml restart kong
- 500 error for "Unknown column users.password_hash": run the migration.
- bcrypt backend errors: rebuild the api image after requirements.txt changes.
- Docker network not found: bring stack down and up again.
