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

RUN apk add --no-cache linux-headers $PHPIZE_DEPS php-pear

RUN docker-php-ext-install mysqli pdo pdo_mysql sockets

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN curl https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer
RUN composer self-update

RUN apk add --no-cache python3 py3-pip ffmpeg yt-dlp

WORKDIR /code
