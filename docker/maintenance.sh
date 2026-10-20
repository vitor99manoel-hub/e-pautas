#!/bin/sh
set -eu
cd /var/www/html
exec php -S "0.0.0.0:${PORT:-10000}" docker/maintenance.php
