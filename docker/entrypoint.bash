#!/usr/bin/env bash

stopServices() {
    echo "Stopping services..."
    supervisorctl stop all
    apache2ctl stop
    echo "Services stopped."
}

cleanup() {
    echo "Container stopped, performing cleanup..."
    php bin/console app:manage-objects --uninstall
}

cd /var/www/html || exit 1

rm -rf var
php bin/console cache:clear
php bin/console cache:warmup

[ ! -d "$DATABASE_DIR" ] && mkdir -p "$DATABASE_DIR"
php bin/console doctrine:migrations:migrate -n

php bin/console app:manage-objects --install

/usr/bin/supervisord
supervisorctl reread
supervisorctl update
supervisorctl start messenger-consume:*
supervisorctl start webhook-listener:*


# Start Apache in the background
apache2ctl start

# Trap SIGTERM and perform cleanup and stop
trap 'stopServices; cleanup' SIGTERM SIGINT

# Wait indefinitely while apache is still running
while :; do [[ "$(ps -A)" =~ apache2 ]] || break; sleep 5; done
