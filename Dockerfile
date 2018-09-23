FROM php:7.2-cli-alpine

LABEL maintainers="me@dyl.dog"
LABEL version="1.0.0"

COPY --from=composer:1.6 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY composer.json composer.lock ./

RUN composer install

COPY . ./

CMD [ "/usr/local/bin/php", "clover-merge.php" ]