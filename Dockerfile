FROM php:8.2-apache

ENV TZ=Europe/Prague
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get -y update && apt-get -y upgrade && apt-get -y install \
    curl \
    zip \
    git \
    zlib1g-dev \
    libzip-dev

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get -y clean
RUN apt-get -y autoclean
RUN apt-get -y autoremove

RUN a2enmod ssl && a2enmod socache_shmcb
RUN a2enmod rewrite

COPY build/dev/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /app