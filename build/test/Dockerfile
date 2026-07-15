FROM php:8.5-fpm-alpine

ENV TERM=xterm-256color
ENV TZ=Europe/Prague
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN adduser -D -u 1000 -s /bin/sh robbyte

RUN apk update && apk add --no-cache \
    bash \
    curl \
    git \
    linux-headers \
    libpq-dev \
    libzip-dev \
    zlib-dev \
    libxml2-dev \
    icu-dev \
    && rm -rf /var/cache/apk/*

RUN docker-php-ext-install sockets pdo pdo_mysql mysqli zip intl bcmath && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app/src

CMD ["php", "-v"]
