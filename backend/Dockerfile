FROM dunglas/frankenphp:1.12.7-php8.3-bookworm

RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    intl \
    zip

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

COPY . .

RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && php artisan package:discover \
    && mkdir -p storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV SERVER_NAME=:8000

COPY Caddyfile /etc/frankenphp/Caddyfile

EXPOSE 8000
