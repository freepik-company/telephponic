FROM europe-west1-docker.pkg.dev/fc-shared/tech/php-cli-8.3-alpine-3.18:latest AS base

RUN apk add --update --no-cache --virtual .build-deps unzip automake cmake make rsync openssh-client esh mysql-client gettext
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
