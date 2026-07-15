FROM dunglas/frankenphp:1-php8.5-alpine

ENV TERM=xterm-256color
ENV TZ=Europe/Prague
ENV SHELL=/bin/bash

# System deps + PHP extensions + timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
    && apk add --no-cache \
        tzdata nodejs npm bash curl git openssh-client \
        linux-headers libzip-dev zlib-dev \
        libxml2-dev icu-dev freetype-dev \
        libjpeg-turbo-dev libpng-dev libwebp-dev \
    && install-php-extensions \
        pdo pdo_mysql mysqli \
        zip intl bcmath gd sockets xdebug

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app/src

# User setup + aliases
RUN adduser -D -u 1000 -s /bin/bash robbyte \
    && chown -R robbyte:robbyte /data /config \
    && echo 'alias ll="ls -lah"' >> /home/robbyte/.bashrc \
    && echo 'alias c="XDEBUG_MODE=off composer"' >> /home/robbyte/.bashrc \
    && echo 'alias cx="composer"' >> /home/robbyte/.bashrc \
    && echo 'alias php="XDEBUG_MODE=off /usr/local/bin/php"' >> /home/robbyte/.bashrc \
    && echo 'alias phpx="/usr/local/bin/php"' >> /home/robbyte/.bashrc \
    && echo "PS1='\e[92m\u\e[0m@\e[94m\h\e[0m:\e[35m\w\e[0m# '" >> /home/robbyte/.bashrc

USER robbyte

EXPOSE 80 443
COPY --chmod=755 --chown=robbyte:robbyte build/dev/entrypoint.sh /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
