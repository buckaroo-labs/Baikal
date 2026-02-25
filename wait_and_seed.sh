#!/bin/bash
#With help from:
#https://www.tutorialpedia.org/blog/docker-wait-for-postgresql-to-be-running/
touch /tmp/build.log 
# Wait until database is up
echo "Waiting for database ..."
until mysqladmin ping --ssl-verify-server-cert=false -u root -pdavmysqlpwd -h db; do
  >&2 echo "database is unavailable (retrying in 1 second)..."
  echo "database is unavailable (retrying in 1 second)..." >> /tmp/build.log
  sleep 1
done
#Create database tables 
>&2 echo "database is up and ready! Seeding database..."
php /var/www/init-db.php >> /tmp/build.log

