#!/usr/bin/env bash
yum -y install php-apc
yum -y install php-mcrypt
yum -y install php-mbstring
yum -y remove php-pecl-memcached
yum -y install php-pecl-memcache

yum -y install npm
npm install -g less
npm install -g uglify-js
npm install -g uglifycss

yum -y install ant
yum -y install ant-apache-regexp

yum -y install rubygems
gem install capifony

chmod -R 777 /var/lib/php/session

cat /vagrant/app/config/provisioning/development.db.sql | mysql -u root

cd /home/vagrant
openssl req -batch -x509 -nodes -days 365 -newkey rsa:2048 -keyout surf.key -out surf.crt
echo 'Include conf.d/*.vhost' >> /etc/httpd/conf/httpd.conf
cp /vagrant/app/config/provisioning/development.vhost.conf /etc/httpd/conf.d/surf.dev.vhost
service httpd restart

cd /vagrant

mkdir -p app/data

rm -rf app/cache/prod* app/cache/dev*

setfacl -R -m u:vagrant:rwX /dev/shm && sudo setfacl -dR -m u:vagrant:rwX /dev/shm

cp /vagrant/app/config/provisioning/development.parameters.yml /vagrant/app/config/parameters.yml

curl -sS https://getcomposer.org/installer | php

php composer.phar install

php app/console doctrine:schema:create
php app/console doctrine:fixtures:load -n
php app/console lexik:translations:import -g -c
php app/console ass:dump -e prod
php app/console cache:clear
php app/console ass:dump
