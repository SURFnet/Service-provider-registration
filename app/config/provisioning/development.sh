yum install php-apc
yum install php-mcrypt
yum install php-mbstring

yum install npm
npm install -g less
npm install -g uglify-js
npm install -g uglifycss

yum install ant
yum install ant-apache-regexp

yum install rubygems
gem install capifony

chmod -R 777 /var/lib/php/session

cat /vagrant/app/config/provisioning/development.db.sql | mysql -u root

cd /home/vagrant
openssl req -batch -x509 -nodes -days 365 -newkey rsa:2048 -keyout surf.key -out surf.crt
echo 'Include conf.d/*.vhost' >> /etc/httpd/conf/httpd.conf
cp /vagrant/app/config/provisioning/development.vhost.conf /etc/httpd/conf.d/surf.dev.vhost
service httpd restart

cd /vagrant

mkdir -p app/data

rm -rf app/cache/prod* app/cache/dev*

setfacl -R -m u:vagrant:rwX app/cache app/logs /dev/shm app/data && sudo setfacl -dR -m -m u:vagrant:rwX app/cache app/logs /dev/shm app/data

cp /vagrant/app/config/provisioning/development.parameters.yml /vagrant/app/config/parameters.yml

curl -sS https://getcomposer.org/installer | php

php composer.phar install

php app/console doctrine:schema:create
php app/console doctrine:fixtures:load
php app/console lexik:translations:import -g -c
php app/console ass:dump -e prod
php app/console cache:clear
php app/console ass:dump
