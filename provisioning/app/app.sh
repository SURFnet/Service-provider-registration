#!/usr/bin/env bash
set -e

cat /vagrant/provisioning/app/db.sql | mysql -u root

cp /vagrant/provisioning/app/*.conf /etc/httpd/conf.d/
service httpd restart

cd /vagrant

mkdir -p app/data

rm -rf app/cache/prod* app/cache/dev*

setfacl -R -m u:vagrant:rwX /dev/shm && sudo setfacl -dR -m u:vagrant:rwX /dev/shm

cp /vagrant/provisioning/app/parameters.yml /vagrant/app/config/parameters.yml

curl -sS https://getcomposer.org/installer | php

php composer.phar install

php app/console doctrine:schema:create
php app/console doctrine:fixtures:load -n
php app/console lexik:translations:import -g -c
php app/console ass:dump -e prod
php app/console cache:clear
php app/console ass:dump
