#!/bin/sh

# cache for production
php artisan route:cache \
    && php artisan config:clear \
    && php artisan config:cache \
    && php artisan view:cache \
    && php artisan event:cache

exec "$@"
