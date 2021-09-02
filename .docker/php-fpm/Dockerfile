# remember that the composer docker-image version in the docker-compose.yml needs to match this version !
FROM php:7.4-fpm

RUN apt-get update
RUN apt-get install -y libzip-dev libpq-dev libpng-dev libmemcached-dev
RUN apt-get clean

# A lot is coming out of the box, e.g. ctype, curl, fileinfo, iconv, json, mbstring, openssl, pdo, tokenizer, xml, ...
# If you know you're going to use pdo_mysql, you can remove mysqli (and vice versa!)
ARG PHP_EXTENSIONS="bcmath gd mysqli pdo_mysql pdo_pgsql zip"
RUN docker-php-ext-install $PHP_EXTENSIONS

# It's unlikely that you need both, redis and memcached. Remember to remove what you don't need!
ARG PECL_EXTENSIONS="redis memcached"
RUN pecl install $PECL_EXTENSIONS && docker-php-ext-enable $PECL_EXTENSIONS
