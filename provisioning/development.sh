#!/usr/bin/env bash
set -e

echo "127.0.0.1     serviceregistry.dev.surfconext.nl" >> /etc/hosts

#yum -y -q install vim
#
#source /vagrant/provisioning/scripts/httpd.sh
#source /vagrant/provisioning/scripts/php.sh

source /vagrant/provisioning/serviceregistry/serviceregistry.sh

#source /vagrant/provisioning/app/app-dev.sh
#source /vagrant/provisioning/app/app.sh
