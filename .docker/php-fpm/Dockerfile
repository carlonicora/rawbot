FROM php:8.1.0RC5-fpm-alpine3.14 as base

RUN set -xe \
        && apk add --no-cache --virtual .build-deps \
            $PHPIZE_DEPS \
            bash \
            git \
            supervisor \
            curl \
            g++ \
            make \
            autoconf \
            libmaxminddb \
            gmp-dev

RUN docker-php-ext-install mysqli bcmath opcache gmp \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && export COMPOSER_PROCESS_TIMEOUT=600 \
    && addgroup -g 1000 -S dockeruser \
    && adduser -u 1000 -S dockeruser -G dockeruser

COPY ./shell/ /tmp/scripts/
RUN chmod -R 777 /tmp/scripts/

RUN touch /var/log/supervisord.log \
    && chown -R dockeruser:dockeruser /var/log/supervisord.log

COPY ./php-fpm/configurations/*.ini /usr/local/etc/php/conf.d/

FROM base as prd
ARG RELEVANCER_WORKERS
COPY ./php-fpm/production-configurations/* /usr/local/etc/php/conf.d/
COPY ./shell/configurations/supervisord.conf /etc/supervisord.conf
RUN rm -rf /tmp/*
CMD ["/usr/bin/supervisord","--configuration=/etc/supervisord.conf"]

FROM base as dev
ARG XDEBUG_PORT
ARG XDEBUG_HOST
COPY ./php-fpm/development-configurations/* /usr/local/etc/php/conf.d/
COPY ./shell/configurations/supervisord.dev.conf /etc/supervisord.conf
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && /tmp/scripts/distributeConfig.sh /usr/local/etc/php/conf.d/zz-xdebug.ini "{XDEBUG_PORT}" "${XDEBUG_PORT}" \
    && /tmp/scripts/distributeConfig.sh /usr/local/etc/php/conf.d/zz-xdebug.ini "{XDEBUG_HOST}" "${XDEBUG_HOST}" \
    && docker-php-source delete \
    && rm -rf /tmp/*

CMD ["/usr/bin/supervisord","--configuration=/etc/supervisord.conf"]