#!/usr/bin/env bash
set -e

echo "Installing SSL enabled Apache 2.4"
openssl req -batch \
            -x509 \
            -nodes \
            -days 365 \
            -newkey rsa:2048 \
            -keyout /etc/pki/tls/private/surf.key \
            -out /etc/pki/tls/certs/surf.crt

yum -y -q install httpd mod_ssl
cp /vagrant/provisioning/scripts/httpd.conf /etc/httpd/conf/httpd.conf
systemctl start httpd.service
systemctl enable httpd.service
