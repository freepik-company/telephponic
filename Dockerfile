FROM php:8.2-fpm-bullseye AS base

RUN apt update && \
    apt install -y git \
    libz-dev

RUN pecl install opentelemetry-beta grpc && docker-php-ext-enable opentelemetry && \
    docker-php-ext-enable opcache && \
    docker-php-ext-enable grpc

FROM base AS dev

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY rootfs/dev /