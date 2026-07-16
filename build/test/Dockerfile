FROM dunglas/frankenphp:1-php8.5-alpine

ENV TERM=xterm-256color
ENV TZ=Europe/Prague
ENV COMPOSER_ALLOW_SUPERUSER=1

# System deps + PHP extensions + timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
    && apk add --no-cache \
        bash \
        curl \
        git \
        linux-headers \
        libzip-dev \
        zlib-dev \
        libxml2-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
    && install-php-extensions \
        pdo pdo_mysql mysqli \
        zip intl bcmath gd sockets

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app/src

CMD ["php", "-v"]
