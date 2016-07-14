#!/usr/bin/env bash
set -e

openssl req -batch -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/pki/tls/private/surf.key -out /etc/pki/tls/certs/surf.crt

chkconfig httpd on
