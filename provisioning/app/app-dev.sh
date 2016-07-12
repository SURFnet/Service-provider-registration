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

# @todo broken:
# ==> default: ERROR:  Error installing capifony:
# ==> default: 	highline requires Ruby version >= 1.9.3.

#yum -y install rubygems
#gem install capifony
