# Set master image
FROM php:8.4-fpm-alpine

RUN docker-php-ext-install mysqli && \
    docker-php-ext-install pdo && \
    docker-php-ext-install pdo_mysql

RUN curl https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer
RUN composer self-update

RUN apk add --no-cache python3 py3-pip ffmpeg yt-dlp

WORKDIR /code
