FROM php:8.2-fpm-alpine

RUN apk add --no-cache postgresql-dev git zip unzip curl \
    && docker-php-ext-install pdo_pgsql bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache \
    && cp .env.example .env \
    && composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader \
    && php artisan key:generate \
    && php artisan storage:link || true \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan serve --host=0.0.0.0 --port=8080
