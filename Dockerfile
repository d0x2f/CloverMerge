FROM php:7.2-cli-alpine

LABEL maintainers="me@dyl.dog"

COPY --from=composer:1.6 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-suggest --no-interaction

COPY . ./

RUN composer dump-autoload --optimize

ENTRYPOINT [ "./clover-merge" ]