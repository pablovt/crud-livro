FROM php:8.4-fpm-alpine

RUN apk add --no-cache git unzip icu-libs icu-data-full libzip mariadb-client \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl bcmath opcache zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/app.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint

WORKDIR /app
ENV COMPOSER_HOME=/tmp/composer

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
