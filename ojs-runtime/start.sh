#!/bin/sh
set -eu
umask 027
php /opt/fronstellar/configure.php
port="${PORT:-8080}"
case "$port" in ''|*[!0-9]*) echo 'Invalid HTTP port' >&2; exit 1;; esac
sed -i "s/^Listen .*/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf
cat >/etc/cron.d/fronstellar <<'CRON'
* * * * * www-data test -f /var/ojs/config/installed && cd /var/www/html && /usr/local/bin/php lib/pkp/tools/scheduler.php run >>/var/ojs/scheduler.log 2>&1
CRON
chmod 0644 /etc/cron.d/fronstellar
cron
exec apache2-foreground
