# Set master image
FROM php:8.4-fpm-alpine

# Install GD dependencies and extension
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

RUN docker-php-ext-install mysqli && \
    docker-php-ext-install pdo && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install sockets

RUN apk add --no-cache $PHPIZE_DEPS php-pear linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN curl https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer
RUN composer self-update

RUN apk add --no-cache python3 py3-pip ffmpeg yt-dlp

WORKDIR /code
