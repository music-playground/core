FROM php:8.2-cli-alpine

WORKDIR /app

RUN apk add --no-cache \
    autoconf \
    bash \
    bison \
    re2c \
    libzip-dev \
    gcc \
    g++ \
    make \
    libc-dev \
    pkgconfig \
    zlib-dev \
    curl-dev \
    openssl-dev \
    linux-headers \
    rabbitmq-c-dev

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN pecl install amqp && docker-php-ext-enable amqp
RUN docker-php-ext-install pcntl

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN alias composer='XDEBUG_MODE=off \composer'

RUN wget https://get.symfony.com/cli/installer -O - | bash && \
     mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

ENV PHP_IDE_CONFIG "serverName=Docker"
ENV XDEBUG_MODE "debug"
ENV XDEBUG_CONFIG "client_host=host.docker.internal client_port=9999"

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]