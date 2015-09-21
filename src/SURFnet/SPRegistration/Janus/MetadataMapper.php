<?php

namespace SURFnet\SPRegistration\Janus;

use AppBundle\Entity\Subscription;
use AppBundle\Model\Contact;
use SAML2_Certificate_X509;
use SAML2_Utilities_Certificate;
use SURFnet\SPRegistration\ServiceRegistry\Constants as ServiceRegistry;

/**
 * Class MetadataMapper
 * @package SURFnet\SPRegistration\Janus
 */
class MetadataMapper
{
    /**
     * @param Subscription $request
     * @return array
     */
    public function mapRequestToMetadata(Subscription $request)
    {
        $width = null;
        $height = null;
        if ($request->getLogoUrl()) {
            // @todo we are guaranteed a working URL here, but the network is not
            //       reliable so we should check anyway.
            list($width, $height) = getimagesize($request->getLogoUrl());
        }

        $certData = '';
        if ($request->getCertificate()) {
            $matches = array();
            preg_match(SAML2_Utilities_Certificate::CERTIFICATE_PATTERN, $request->getCertificate(), $matches);
            $key = SAML2_Certificate_X509::createFromCertificateData($matches[1]);
            $certData = $key['X509Certificate'];
        }

        $data = array(
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

            ServiceRegistry::ASSERTIONCONSUMERSERVICE_0_BINDING => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ServiceRegistry::ASSERTIONCONSUMERSERVICE_0_LOCATION => $request->getAcsLocation(),
            ServiceRegistry::CERTDATA => $certData,
        );

        $technicalContact = $request->getTechnicalContact();
        if (!$technicalContact) {
            $technicalContact = new Contact();
        }
        $data[ServiceRegistry::CONTACTS_0_CONTACTTYPE] = ServiceRegistry::CONTACT_TYPE_TECHNICAL;
        $data[ServiceRegistry::CONTACTS_0_EMAILADDRESS]     = $technicalContact->getEmail();
        $data[ServiceRegistry::CONTACTS_0_GIVENNAME]        = $technicalContact->getFirstName();
        $data[ServiceRegistry::CONTACTS_0_SURNAME]          = $technicalContact->getLastName();
        $data[ServiceRegistry::CONTACTS_0_TELEPHONENUMBER]  = $technicalContact->getPhone();

        $supportContact = $request->getSupportContact();
        if (!$supportContact) {
            $supportContact = new Contact();
        }
        $data[ServiceRegistry::CONTACTS_1_CONTACTTYPE]      = ServiceRegistry::CONTACT_TYPE_SUPPORT;
        $data[ServiceRegistry::CONTACTS_1_EMAILADDRESS]     = $supportContact->getEmail();
        $data[ServiceRegistry::CONTACTS_1_GIVENNAME]        = $supportContact->getFirstName();
        $data[ServiceRegistry::CONTACTS_1_SURNAME]          = $supportContact->getLastName();
        $data[ServiceRegistry::CONTACTS_1_TELEPHONENUMBER]  = $supportContact->getPhone();

        $administrativeContact = $request->getAdministrativeContact();
        if (!$administrativeContact) {
            $administrativeContact = new Contact();
        }
        $data[ServiceRegistry::CONTACTS_2_CONTACTTYPE]      = ServiceRegistry::CONTACT_TYPE_ADMINISTRATIVE;
        $data[ServiceRegistry::CONTACTS_2_EMAILADDRESS]     = $administrativeContact->getEmail();
        $data[ServiceRegistry::CONTACTS_2_GIVENNAME]        = $administrativeContact->getFirstName();
        $data[ServiceRegistry::CONTACTS_2_SURNAME]          = $administrativeContact->getLastName();
        $data[ServiceRegistry::CONTACTS_2_TELEPHONENUMBER]  = $administrativeContact->getPhone();

        return $data;
    }
}
