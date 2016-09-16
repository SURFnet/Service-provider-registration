#!/usr/bin/env bash
set -e

yum -y install npm
npm install -g -q n
n stable
npm install -g -q less
npm install -g -q uglify-js
npm install -g -q uglifycss

yum -y -q install ant
yum -y -q install ant-apache-regexp

yum -y install rubygems
gem install highline --version "=1.6.2"
gem install net-ssh --version "=2.9.2"
gem install capifony
