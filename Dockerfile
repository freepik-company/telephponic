FROM europe-west1-docker.pkg.dev/fc-shared/tech/php-cli-8.2-alpine-3.17:latest AS base

RUN apt update && apt install -y  && pecl install  &&

RUN pecl install  && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
