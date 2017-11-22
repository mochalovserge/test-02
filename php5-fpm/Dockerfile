FROM php:5.6.32-fpm
MAINTAINER mochalov.serge <mochalov.serge@gmail.com>

RUN apt-get update && apt-get install -y \
    libmcrypt-dev \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install -j$(nproc) mcrypt \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install exif
RUN docker-php-ext-install opcache
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

CMD ["php-fpm"]