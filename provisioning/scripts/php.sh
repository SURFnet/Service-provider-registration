#!/usr/bin/env bash
set -e

yum -y -q install php-apc php-gd php-mbstring php-mcrypt php-pecl-xdebug

yum -y -q remove php-pecl-memcached
yum -y -q install php-pecl-memcache

chmod -R 777 /var/lib/php/session

cp /vagrant/provisioning/scripts/xdebug.ini /etc/php.d/xdebug.ini
