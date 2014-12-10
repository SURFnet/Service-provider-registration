SURFnet / Service-provider-registration
========================

Welcome!

Requirements
----------------------------------

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
        
            Alias /simplesaml [PROJECT-DIR]/current/vendor/simplesamlphp/simplesamlphp/www
        
            DocumentRoot [PROJECT-DIR]/current/web
            <Directory [PROJECT-DIR]/current/web>
                AllowOverride All
                Order allow,deny
                Allow from All
            </Directory>
        </VirtualHost>
        
* HTTPS/SSL is highly recommended

### MySQL

### SMTP

Environments
----------------------------------

### Setup Development
* git clone git@github.com:SURFnet/Service-provider-registration.git .
* vagrant up
* vagrant ssh
* sudo yum install npm
* sudo npm install -g less
* sudo npm install -g uglify-js
* sudo npm install -g uglifycss
* sudo yum install ant
* sudo yum install ant-apache-regexp
* cd /vagrant
* curl -s https://getcomposer.org/installer | php
* composer install
* sudo setfacl -R -m u:apache:rwX -m u:vagrant:rwX app/cache app/logs /dev/shm app/data && sudo setfacl -dR -m u:apache:rwX -m u:vagrant:rwX app/cache app/logs /dev/shm app/data
* php app/console doctrine:schema:create --force
* php app/console doctrine:fixtures:load
* php app/console lexik:translations:import -g -c
* php app/console cache:clear
* php app/console ass:dump

### Setup Deploy env (@ SURFnet)
* install capifony
* cd [PROJECT-DIR]
* git clone git@github.com:SURFnet/Service-provider-registration.git .

### Setup Test/Staging/Prod (@ SURFnet)
* @Server: configure apache/mysql etc
* @Deploy env: cd [PROJECT-DIR]
* @Deploy env: cap deploy:setup
* @Deploy env: cap deploy
* @Deploy env: cap symfony:doctrine:load_fixtures
* @Deploy env: cap deploy
* @Server: Add to crontab: php app/console app:mail:report --env=prod 

Configuration
----------------------------------

Configuration parameters will initially be set by composer install. Afterwards they can be adjusted in app/config/parameters.yml.

Logs can be found in app/logs.

Deployment
----------------------------------

### Prepare Release (@ development)
* Switch to release branch
* Merge in master
* rm -rf web/js/compiled web/css/compiled web/compiled/css web/compiled/js
* php app/console ass:dump -e prod
* git commit/push

### Deploy release (@ SURFnet deploy env) - Automatic using Capifony
* cd [PROJECT-DIR]
* git clone new or pull existing checkout
* cap deploy

Test Data
----------------------------------

Metadata URL's:

* https://www.meertens.knaw.nl/Shibboleth.sso/Metadata
* https://sts.hva.nl/federationmetadata/2007-06/federationmetadata.xml
* https://info.acc.hsleiden.nl/OpenSAML.sso/Metadata
* https://dmsonline.uvt.nl/simplesaml/module.php/saml/sp/metadata.php/surfconext-uvt 
