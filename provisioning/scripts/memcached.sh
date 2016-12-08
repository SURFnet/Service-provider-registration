#!/usr/bin/env bash

echo "Installing memcached server"
yum -y -q install memcached
systemctl start memcached
systemctl enable memcached
