#!/bin/sh
set -eu

mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
ln -sfn ../storage/app/public public/storage
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
