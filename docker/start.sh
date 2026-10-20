#!/bin/sh
set -eu
cd /var/www/html
# Discard build-time configuration before inspecting the runtime connection.
php artisan config:clear
php artisan storage:verify
# Schema changes are an explicit operator step after backup, never an image build.
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
