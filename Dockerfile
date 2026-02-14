FROM python:3.11-slim

# Reference-only Dockerfile for the main branch.
# The actual build files live in each branch:
# - FastAPI-with-Kong
# - Laravel-with-Kong
#
# Redis add-ons (branch-specific):
# FastAPI: add `redis` to requirements.txt or `pip install redis`.
# Laravel: install php-redis extension (or use predis) in the Laravel Dockerfile.

WORKDIR /app

COPY requirements.txt /app/requirements.txt
RUN pip install --no-cache-dir -r /app/requirements.txt

COPY src /app/src

EXPOSE 8000

CMD ["uvicorn", "src.app.main:app", "--host", "0.0.0.0", "--port", "8000"]
