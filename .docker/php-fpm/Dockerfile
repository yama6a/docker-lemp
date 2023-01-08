ARG IMAGE=dev

# remember that the composer docker-image version in the docker-compose.yml needs to match this version !
FROM php:8.1-fpm AS dev

RUN apt-get update
RUN apt-get install -y libzip-dev libpq-dev libpng-dev libmemcached-dev
RUN apt-get clean

# A lot is coming out of the box, e.g. ctype, curl, fileinfo, iconv, json, mbstring, openssl, pdo, tokenizer, xml, ...
# If you know you're going to use pdo_mysql, you can remove mysqli (or vice versa!)
ARG PHP_EXTENSIONS="bcmath gd mysqli pdo_mysql pdo_pgsql zip"
RUN docker-php-ext-install $PHP_EXTENSIONS

# It's unlikely that you need both, redis and memcached. Remember to remove what you don't need!
ARG PECL_EXTENSIONS="redis memcached"
RUN pecl install $PECL_EXTENSIONS && docker-php-ext-enable $PECL_EXTENSIONS

# Ini Settings
RUN apt-get install -y python3-pip

# DEBUGGING tools for PostGres
#RUN pip install pgcli
#RUN echo "memory_limit = 512M" >> "$PHP_INI_DIR/php.ini"

# This part only gets called if you call `docker build` with --build-arg IMAGE=deploy.
# In development mode (using docker-copmose), we don't use that build-arg but mount the folder instead,
# rather than copying the files, so we don't have to rebuild the image every time we make a change.
FROM dev as deploy
WORKDIR /
ONBUILD COPY src /code/
ONBUILD COPY .docker/postgres/docker-entrypoint-initdb.d/* /code/pg_migrations/

FROM ${IMAGE}
