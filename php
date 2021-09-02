#!/usr/bin/env bash

docker-compose run --workdir="/code" --rm php-fpm php $1
