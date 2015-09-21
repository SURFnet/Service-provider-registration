<?php

namespace SURFnet\SPRegistration\Janus;

use AppBundle\Entity\Subscription;
use SAML2_Certificate_X509;
use SAML2_Utilities_Certificate;
use SURFnet\SPRegistration\ServiceRegistry\Constants as ServiceRegistry;

class MetadataMapper
{
    /**
     * @param Subscription $request
     * @return array
     */
    public function mapRequestToMetadata(Subscription $request)
    {
        // @todo we are guaranteed a working URL here, but the network is not
        //       reliable so we should check anyway.
        list($width, $height) = getimagesize($request->getLogoUrl());

        $certData = '';
        if ($request->getCertificate()) {
            $matches = array();
            preg_match(SAML2_Utilities_Certificate::CERTIFICATE_PATTERN, $request->getCertificate(), $matches);
            $key = SAML2_Certificate_X509::createFromCertificateData($matches[1]);
            $certData = $key['X509Certificate'];
        }

        return array(
            ServiceRegistry::NAME_EN => $request->getNameEn(),
            ServiceRegistry::NAME_NL => $request->getNameNl(),

            ServiceRegistry::DESCRIPTION_EN => $request->getDescriptionEn(),
            ServiceRegistry::DESCRIPTION_NL => $request->getDescriptionNl(),
            ServiceRegistry::URL_NL => $request->getApplicationUrl(),
            ServiceRegistry::URL_EN => $request->getApplicationUrl(),
            ServiceRegistry::COIN_EULA => $request->getEulaUrl(),

            ServiceRegistry::NAMEIDFORMAT => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

            ServiceRegistry::LOGO_0_URL => $request->getLogoUrl(),
            ServiceRegistry::LOGO_0_WIDTH => $width,
            ServiceRegistry::LOGO_0_HEIGHT => $height,

            ServiceRegistry::CONTACTS_0_CONTACTTYPE => ServiceRegistry::CONTACT_TYPE_TECHNICAL,
            ServiceRegistry::CONTACTS_0_EMAILADDRESS => $request->getTechnicalContact()->getEmail(),
            ServiceRegistry::CONTACTS_0_GIVENNAME => $request->getTechnicalContact()->getFirstName(),
            ServiceRegistry::CONTACTS_0_SURNAME => $request->getTechnicalContact()->getLastName(),
            ServiceRegistry::CONTACTS_0_TELEPHONENUMBER => $request->getTechnicalContact()->getPhone(),

            ServiceRegistry::CONTACTS_1_CONTACTTYPE => ServiceRegistry::CONTACT_TYPE_SUPPORT,
            ServiceRegistry::CONTACTS_1_EMAILADDRESS => $request->getSupportContact()->getEmail(),
            ServiceRegistry::CONTACTS_1_GIVENNAME => $request->getSupportContact()->getFirstName(),
            ServiceRegistry::CONTACTS_1_SURNAME => $request->getSupportContact()->getLastName(),
            ServiceRegistry::CONTACTS_1_TELEPHONENUMBER => $request->getSupportContact()->getPhone(),

            ServiceRegistry::CONTACTS_2_CONTACTTYPE => ServiceRegistry::CONTACT_TYPE_ADMINISTRATIVE,
            ServiceRegistry::CONTACTS_2_EMAILADDRESS => $request->getAdministrativeContact()->getEmail(),
            ServiceRegistry::CONTACTS_2_GIVENNAME => $request->getAdministrativeContact()->getFirstName(),
            ServiceRegistry::CONTACTS_2_SURNAME => $request->getAdministrativeContact()->getLastName(),
            ServiceRegistry::CONTACTS_2_TELEPHONENUMBER => $request->getAdministrativeContact()->getPhone(),

            ServiceRegistry::ASSERTIONCONSUMERSERVICE_0_BINDING => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ServiceRegistry::ASSERTIONCONSUMERSERVICE_0_LOCATION => $request->getAcsLocation(),
            ServiceRegistry::CERTDATA => $certData,
        );
    }
}
