SURFnet / Service-provider-registration
========================

Welcome!

Requirements
----------------------------------

### System
* sudo yum install npm
* sudo npm install -g less
* sudo npm install -g uglify-js
* sudo npm install -g uglifycss

### PHP
* min. PHP 5.3.3
* apc
* mbstring
* mcrypt

### Apache
* vhost

        <VirtualHost *:80>
            ServerName [HOSTNAME]
            ServerAlias www.[HOSTNAME]
        
            Alias /simplesaml [PROJECT-DIR]/vendor/simplesamlphp/simplesamlphp/www
        
            DocumentRoot [PROJECT-DIR]/web
            <Directory [PROJECT-DIR]/web>
                AllowOverride All
                Order allow,deny
                Allow from All
            </Directory>
        </VirtualHost>

### MySQL

Environments
----------------------------------

### Setup Development
* git clone ....
* vagrant up
* vagrant ssh
* cd /vagrant
* curl -s https://getcomposer.org/installer | php
* composer install
* sudo setfacl -R -m u:apache:rwX -m u:vagrant:rwX app/cache app/logs /dev/shm app/data && sudo setfacl -dR -m u:apache:rwX -m u:vagrant:rwX app/cache app/logs /dev/shm app/data
* php app/console doctrine:schema:create --force
* php app/console doctrine:fixtures:load
* php app/console lexik:translations:import -g -c
* php app/console cache:clear
* php app/console ass:dump

### Setup Test/Staging/Prod
* cd [PROJECT-DIR]
* export SYMFONY_ENV=prod
* git clone ....
* curl -s https://getcomposer.org/installer | php
* php composer.phar install -o --no-dev
* HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
* sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs app/data
* sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs app/data
* php app/console doctrine:schema:create --force
* php app/console doctrine:fixtures:load
* php app/console lexik:translations:import -g -c
* php app/console ass:dump
* php app/console cache:clear
* Add to crontab: php app/console app:mail:report --env=prod 

Deployment
----------------------------------

For now project is hosted on vps20.ibuildings.com

### Automatic (Capifony)
* cap deploy (uses ssh key forwarding for user root@vps20)

### Manual
* cd [PROJECT-DIR]
* export SYMFONY_ENV=prod
* git pull
* php composer.phar composer install -o --no-dev
* vi app/config.yml -> raise 'assets_version'
* php app/console ass:dump
* php app/console doctrine:schema:update
* php app/console lexik:translations:import -g -c
* php app/console cache:warmup

Test Data
----------------------------------

Metadata URL's:

* https://www.meertens.knaw.nl/Shibboleth.sso/Metadata
* https://sts.hva.nl/federationmetadata/2007-06/federationmetadata.xml
* https://info.acc.hsleiden.nl/OpenSAML.sso/Metadata
* https://dmsonline.uvt.nl/simplesaml/module.php/saml/sp/metadata.php/surfconext-uvt 
