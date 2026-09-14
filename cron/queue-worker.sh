#!/bin/sh

cd "$(dirname "$0")/.." || exit 1

/usr/bin/php artisan queue:work \
    --stop-when-empty \
    --sleep=3 \
    --tries=3 \
    --timeout=120