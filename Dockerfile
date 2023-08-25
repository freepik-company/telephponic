FROM europe-west1-docker.pkg.dev/fc-shared/tech/php-cli-8.2-alpine-3.17:latest AS base

FROM base AS dev

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
