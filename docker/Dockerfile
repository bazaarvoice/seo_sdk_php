FROM php:7.4-fpm
LABEL maintainer="boitata@leroymerlin.com.br"

USER root:root

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV PATH=$PATH:/var/www/.composer/vendor/bin

RUN apt-get update -qq \
  && apt-get install -qq --no-install-recommends \
      git zip unzip \
      libzip-dev libssl-dev \
      zlib1g-dev libicu-dev \
  && apt-get clean \
  && docker-php-ext-configure \
    intl \
  && docker-php-ext-install  \
    intl pcntl zip \
  && rm -rf /tmp/*

RUN pecl install xdebug-2.9.8 \
  && docker-php-ext-enable xdebug

ARG UID=1000
ARG GID=1000

RUN groupmod -g ${GID} www-data \
  && usermod -u ${UID} -g www-data www-data \
  && chown -hR www-data:www-data \
    /var/www \
    /usr/local/

COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html
USER www-data:www-data