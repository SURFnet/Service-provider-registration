#!/usr/bin/env bash
set -e

yum -y -q install wget

cd /tmp

rm -rf /var/www/serviceregistry
wget -q https://simplesamlphp.org/res/downloads/simplesamlphp-1.13.2.tar.gz
tar xzf simplesamlphp-1.13.2.tar.gz -C /var/www/
mv /var/www/simplesamlphp-1.13.2 /var/www/serviceregistry

mkdir -p /var/www/serviceregistry/cert
cp -vf /vagrant/provisioning/serviceregistry/config/*.php /var/www/serviceregistry/config/
cp -vf /vagrant/provisioning/serviceregistry/cert/* /var/www/serviceregistry/cert/
cp -vf /vagrant/provisioning/serviceregistry/metadata/*.php /var/www/serviceregistry/metadata/

touch /var/www/serviceregistry/modules/exampleauth/enable

rm -rf /var/www/serviceregistry/modules/janus
mkdir /var/www/serviceregistry/modules/janus
wget -q https://github.com/janus-ssp/janus/releases/download/1.21.0/janus-1.21.0.tar.gz
tar xzf janus-1.21.0.tar.gz -C /var/www/serviceregistry/modules/janus

cp -vf /vagrant/provisioning/serviceregistry/janus/config_janus_core.yml                 /var/www/serviceregistry/modules/janus/app/config/config_janus_core.yml
cp -vf /vagrant/provisioning/serviceregistry/janus/parameters.yml                        /var/www/serviceregistry/modules/janus/app/config/parameters.yml
cp -vf /vagrant/provisioning/serviceregistry/janus/metadatafields-custom.definition.json /var/www/serviceregistry/modules/janus/dictionaries/metadatafields-custom.definition.json

mkdir -p /var/cache/janus-ssp/janus/sessions
chown -Rf vagrant /var/cache/janus-ssp

mkdir -p /var/log/janus-ssp/janus
chown -Rf vagrant /var/log/janus-ssp/janus

cat /vagrant/provisioning/serviceregistry/db.sql | mysql -u root

cd /var/www/serviceregistry/modules/janus && ./bin/migrate.sh
chown -Rf vagrant /var/cache/janus-ssp
chown -Rf vagrant /var/log/janus-ssp/janus

cp /vagrant/provisioning/serviceregistry/*.conf /etc/httpd/conf.d/
service httpd restart
