#!/usr/bin/env bash

echo "Disabling SELinux"

echo 0 > /sys/fs/selinux/enforce
cp /vagrant/provisioning/scripts/selinux.config /etc/selinux/config
