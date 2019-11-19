SURFnet / Service-provider-registration
========================

**Please note that this project has been superseeded by the SP Dashboard, available here: https://github.com/SURFnet/sp-dashboard!**

Requirements
----------------------------------

### PHP
* min. PHP 5.3.3
* apc
* mbstring
* mcrypt

### Apache
* vhost

        <VirtualHost *:443>
            ServerName [HOSTNAME]
            ServerAlias www.[HOSTNAME]
            
            SSLEngine on
            SSLCertificateFile [SSL-CERT-DIR]/[CERT].crt
            SSLCertificateKeyFile [SSL-KEY-DIR]/[KEY].key
        
            Alias /simplesaml [PROJECT-DIR]/current/vendor/simplesamlphp/simplesamlphp/www
        
            DocumentRoot [PROJECT-DIR]/current/web
            <Directory [PROJECT-DIR]/current/web>
                AllowOverride All
                Order allow,deny
                Allow from All
            </Directory>
        </VirtualHost>

### MySQL

### SMTP

### MEMCACHED

Environments
----------------------------------

### Setup Development
* cd [PROJECT-DIR]
* git clone git@github.com:SURFnet/Service-provider-registration.git .
* add to local hosts file: 192.168.33.19 surf.dev
* vagrant up
* Test at: https://dev.support.surfconext.nl/registration/app_dev.php/admin ( log in with admin/admin )
* Administer the Service Registry at: https://serviceregistry.dev.support.surfconext.nl/module.php/janus/dashboard.php/entities ( log in with student/studentpass )

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
