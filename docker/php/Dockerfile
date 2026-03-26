# Set master image
FROM php:8.5-fpm-alpine

# Install GD dependencies and extension
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

RUN apk add --no-cache linux-headers $PHPIZE_DEPS php-pear imagemagick imagemagick-dev

RUN docker-php-ext-install mysqli pdo pdo_mysql sockets exif

RUN pecl install xdebug imagick \
    && mkdir -p /usr/local/etc/php/conf.d \
    && docker-php-ext-enable xdebug imagick

RUN curl https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer
RUN composer self-update

RUN apk add --no-cache python3 py3-pip ffmpeg yt-dlp

WORKDIR /code
