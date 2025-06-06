FROM php:8.4-fpm-alpine

ENV TERM=xterm-256color
ENV TZ=Europe/Prague
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN adduser -D -u 1000 -s /bin/sh robbyte

RUN apk update && apk add --no-cache \
    supervisor \
    nginx \
    nodejs \
    npm \
    bash \
    curl \
    git \
    make \
    linux-headers \
    libpq-dev \
    libzip-dev \
    zlib-dev \
    libxml2-dev \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    && rm -rf /var/cache/apk/*

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && export PHP_IDE_CONFIG="serverName=localhost"

ADD build/dev/certs/tls.crt /usr/local/share/ca-certificates/tls.crt
RUN update-ca-certificates

RUN docker-php-ext-configure gd --with-jpeg --with-webp && \
    docker-php-ext-install gd pdo pdo_mysql mysqli pdo_pgsql pgsql zip intl opcache bcmath && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash -s -- --install-dir=/usr/local/bin \
    && mv /usr/local/bin/symfony* /usr/local/bin/symfony \
    && chmod +x /usr/local/bin/symfony

COPY build/dev/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY build/dev/php/php.ini /usr/local/etc/php/php.ini
COPY build/dev/php/php-fpm.conf /usr/local/etc/php-fpm.d/app.conf
COPY build/dev/nginx/nginx.conf /etc/nginx/nginx.conf
COPY build/dev/nginx/default.conf /etc/nginx/http.d/default.conf
COPY build/dev/supervisord/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY build/dev/supervisord/start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh && \
    addgroup robbyte nginx && \
    mkdir -p /var/lib/nginx && \
    chown -R robbyte:robbyte /var/log/nginx && \
    chown -R robbyte:robbyte /var/lib/nginx && \
    echo 'alias ll="ls -lah"' >> /home/robbyte/.bashrc && \
    echo 'alias c="composer"' >> /home/robbyte/.bashrc && \
    echo 'alias co="XDEBUG_MODE=off php bin/console"' >> /home/robbyte/.bashrc && \
    echo 'alias p="XDEBUG_MODE=off php"' >> /home/robbyte/.bashrc && \
    echo 'alias px="php"' >> /home/robbyte/.bashrc && \
    echo 'alias cox="php bin/console"' >> /home/robbyte/.bashrc && \
    echo "PS1='\e[92m\u\e[0m@\e[94m\h\e[0m:\e[35m\w\e[0m# '" >> /home/robbyte/.bashrc

WORKDIR /app/src
COPY ./src .

RUN chown -R robbyte:robbyte /app/src
USER robbyte

CMD ["/usr/local/bin/start.sh", "-c", "/etc/supervisor/conf.d/supervisord.conf"]