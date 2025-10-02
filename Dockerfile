FROM php:8.2-cli

RUN apt-get update && apt-get install -y libpq-dev zip unzip
RUN docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
