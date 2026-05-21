ARG PHP_BASE_TAG=php-8.5-v1
FROM europe-west1-docker.pkg.dev/galvanic-camp-493316-i2/canalizador-images/php-base:${PHP_BASE_TAG} AS base

# ---------------------------------------------------------------------------
FROM base AS dev

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# ---------------------------------------------------------------------------
FROM base AS prod

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi

RUN chown -R www-data:www-data /code/storage /code/bootstrap/cache

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=192'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
} > /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000
CMD ["php-fpm"]
