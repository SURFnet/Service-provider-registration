#!/usr/bin/env bash
set -e

echo "127.0.0.1     serviceregistry.dev.support.surfconext.nl" >> /etc/hosts
echo "cd /vagrant" >> /home/vagrant/.bash_profile

yum -y -q install vim

source /vagrant/provisioning/scripts/selinux.sh
source /vagrant/provisioning/scripts/memcached.sh
source /vagrant/provisioning/scripts/httpd.sh
source /vagrant/provisioning/scripts/php.sh
source /vagrant/provisioning/scripts/mysql.sh

source /vagrant/provisioning/serviceregistry/serviceregistry.sh

source /vagrant/provisioning/app/app-dev.sh
source /vagrant/provisioning/app/app.sh
