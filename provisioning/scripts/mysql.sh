#!/usr/bin/env bash

echo "Installing MariaDb server and client"
yum -y -q install mariadb mariadb-server
systemctl start mariadb
systemctl enable mariadb.service
