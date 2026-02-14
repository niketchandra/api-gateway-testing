FROM php:8.2-cli

WORKDIR /app

RUN apt-get update \
    ; apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libonig-dev \
    ; docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
    ; pecl install redis \
    ; docker-php-ext-enable redis \
    ; apt-get clean \
    ; rm -rf /var/lib/apt/lists/*

COPY composer /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    ; composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs || composer update --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs \
    ; if [ ! -f .env ]; then cp .env.example .env; fi \
    ; php artisan key:generate --force \
    ; chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]
