FROM php:8.2-fpm-bullseye AS dev

RUN apt update && \
    apt install -y git \
    libz-dev \
    libmemcached-dev

RUN pecl install opentelemetry-beta grpc xdebug redis memcached &&  \
    docker-php-ext-enable opentelemetry opcache grpc redis memcached

RUN apt update && apt install -y  && pecl install  &&

RUN pecl install  && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
