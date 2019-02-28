#!/bin/bash
chown -R postgres:postgres /app/src
stopServices() {
        service apache2 stop
        service postgresql stop
}
trap stopServices TERM

service postgresql start
service apache2 start

# fork a process and wait for it
tail -f /var/log/postgresql/postgresql-9.5-main.log & sudo -u postgres /app/src/build/utils/update.php --import-osmosis-all &
wait
