#!/usr/bin/env bash
set -e

yum -y -q install php-apc php-gd php-mbstring php-mcrypt

yum -y -q remove php-pecl-memcached
yum -y -q install php-pecl-memcache

chmod -R 777 /var/lib/php/session
