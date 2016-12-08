#!/usr/bin/env bash
set -e

echo "Installing PHP 5.6 from Webtatic"
rpm -Uvh --replacepkgs https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
rpm -Uvh --replacepkgs https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
yum -y -q install php56w php56w-gd php56w-mbstring php56w-mcrypt php56w-pecl-xdebug php56w-pecl-memcache php56w-mysql php56w-pecl-apcu

mkdir -p /var/lib/php/session
chmod -R 777 /var/lib/php/session

cp /vagrant/provisioning/scripts/php.ini /etc/php.ini
cp /vagrant/provisioning/scripts/xdebug.ini /etc/php.d/xdebug.ini
