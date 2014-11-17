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

Deployment
----------------------------------

For now project is hosted on vps20.ibuildings.com

### Capifony
* cap deploy (uses ssh key forwarding for user root)

### Manual
* export SYMFONY_ENV=prod
* git pull
* php composer.phar composer install -o --no-dev
* vi app/config.yml -> raise 'assets_version'
* php app/console ass:dump

Test Data
----------------------------------

Metadata URL's:

* https://www.meertens.knaw.nl/Shibboleth.sso/Metadata
* https://sts.hva.nl/federationmetadata/2007-06/federationmetadata.xml
* https://info.acc.hsleiden.nl/OpenSAML.sso/Metadata
* https://dmsonline.uvt.nl/simplesaml/module.php/saml/sp/metadata.php/surfconext-uvt 
