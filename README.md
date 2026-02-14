# Laravel User CRUD with MySQL and Kong

This project provides a complete CRUD API for a User resource using Laravel and MySQL, exposed through Kong API Gateway.

## Repository Branches
- [FastAPI-with-Kong](https://github.com/niketchandra/api-gateway-testing/tree/FastAPI-with-Kong) - FastAPI implementation
- [Laravel-with-Kong](https://github.com/niketchandra/api-gateway-testing/tree/Laravel-with-Kong) - Laravel implementation
- [Redis-Integration](https://github.com/niketchandra/api-gateway-testing/tree/Redis-Integration) - Redis caching layer integration

## Features

### ✨ Resilience & High Availability
This project implements production-ready resilience patterns:

- **Circuit Breaker**: Automatically detects database failures and prevents cascading errors
- **Message Queue**: Buffers write operations when database is unavailable
- **Automatic Retry**: Failed operations retry with exponential backoff (30s, 60s, 120s, 300s, 600s)
- **Graceful Degradation**: Returns meaningful responses even when services are down

**How it works:**
1. When database fails 3 times, circuit breaker opens
2. Write operations are queued in Redis
3. Queue worker processes jobs when database recovers
4. Read operations return 503 with circuit state information

See [IMPLEMENTATION.md](IMPLEMENTATION.md) for complete guide and testing instructions.

## Docs
- Laravel API details: [LARAVEL.md](LARAVEL.md)
- Kong config and routing: [KONG.md](KONG.md)
- Redis integration: [redis.md](redis.md)
- Resilience patterns (Circuit Breakers & Queues): [resilience.md](resilience.md)
- **Implementation Guide (Circuit Breaker + Queue)**: [IMPLEMENTATION.md](IMPLEMENTATION.md)

## Docker Compose (API + MySQL + Redis + Kong + Queue Worker)
1. Start everything:

```bash
docker compose -f docker-compose.yml -f docker-compose-kong.yml up -d
```

2. Services:
- **api**: Laravel application
- **mysql**: MySQL 8.0 database
- **redis**: Redis 7 for caching and queue
- **queue-worker**: Laravel queue worker for background jobs
- **kong**: Kong API Gateway 3.6
- **phpmyadmin**: Database admin interface

3. Endpoints:
- API (direct): http://localhost:8000
- Kong proxy: http://localhost:8002
- Kong admin: http://localhost:8001
- phpMyAdmin: http://localhost:8080 (user root, password empty)
- Redis: localhost:6379

The Laravel application lives in composer/ and is served by the api container.

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

## Products CRUD (via Kong)
Create product:

```bash
curl -X POST http://localhost:8002/products \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Widget\",\"sku\":\"WID-001\",\"price_cents\":1200}"
```

List products:

```bash
curl http://localhost:8002/products
```

Get product:

```bash
curl http://localhost:8002/products/1
```

Update product:

```bash
curl -X PUT http://localhost:8002/products/1 \
  -H "Content-Type: application/json" \
  -d "{\"price_cents\":1500}"
```

Delete product:

```bash
curl -X DELETE http://localhost:8002/products/1
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

## File upload and download (via Kong)
Upload:

```bash
curl -X POST http://localhost:8002/files/upload \
  -F "file=@./path/to/your/file.txt"
```

Download (use file_id from upload response):

```bash
curl -O http://localhost:8002/files/<file_id>
```

## Laravel migrations
Run migrations inside the api container:

```bash
docker compose exec api php artisan migrate --force
```

## Troubleshooting
- Kong says "no Route matched": restart Kong after editing kong/kong.yml.
  - docker compose -f docker-compose.yml -f docker-compose-kong.yml restart kong
- 500 error for "Unknown column users.password_hash": run the migration.
- Docker network not found: bring stack down and up again.
