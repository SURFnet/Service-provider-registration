<?php

use AppBundle\Entity\Subscription;
use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use OpenConext\JanusClient\ArpAttributes;
use OpenConext\JanusClient\ConnectionAccess;
use OpenConext\JanusClient\Entity\Connection;
use SURFnet\SPRegistration\ServiceRegistry\Consts as ServiceRegistry;

class ConnectionRequestTranslator
{
    const PEM_HEADER = '-----BEGIN CERTIFICATE-----';
    const PEM_FOOTER = '-----END CERTIFICATE-----';

    public function translateToConnection(Subscription $request)
    {
        return new Connection(
            $request->getEntityId(),
            Connection::TYPE_SP,
            Connection::WORKFLOW_TEST,
            $this->getMetadataFromRequest($request),
            $request->getMetadataUrl(),
            '',
            new ConnectionAccess(),
            $this->getArpAttributesFromRequest($request)
        );
    }

    public function translateFromConnection(
        Connection $connection,
        Subscription $request
    )
    {
        $request->setNameNl($connection->getMetadata(ServiceRegistry::NAME_NL));
        $request->setNameEn($connection->getMetadata(ServiceRegistry::NAME_EN));
        $request->setDescriptionEn($connection->getMetadata(ServiceRegistry::DESCRIPTION_EN));
        $request->setDescriptionNl($connection->getMetadata(ServiceRegistry::DESCRIPTION_NL));
        $request->setApplicationUrl($connection->getMetadata(ServiceRegistry::URL_EN));
        $request->setEulaUrl($connection->getMetadata(ServiceRegistry::COIN_EULA));
        $request->setLogoUrl($connection->getMetadata(ServiceRegistry::LOGO_0_URL));
        $request->setAdministrativeContact(
            $this->getContactOfType(ServiceRegistry::CONTACT_TYPE_ADMINISTRATIVE, $connection)
        );
        $request->setSupportContact(
            $this->getContactOfType(ServiceRegistry::CONTACT_TYPE_SUPPORT, $connection)
        );
        $request->setTechnicalContact(
            $this->getContactOfType(ServiceRegistry::CONTACT_TYPE_TECHNICAL, $connection)
        );
        $request->setAcsLocation(
            $connection->getMetadata(ServiceRegistry::ASSERTIONCONSUMERSERVICE_0_LOCATION)
        );
        $certData = $connection->getMetadata('certData');
        if ($certData) {
            $request->setCertificate(SAML2_Certificate_X509::createFromCertificateData($certData)->getCertificate());
        }
    }

    private function getMetadataFromRequest(Subscription $request)
    {
        // @todo what happens on a 404?
        list($width, $height) = getimagesize($request->getLogoUrl());

        return array(
            ServiceRegistry::NAME_EN                                => $request->getNameEn(),
            ServiceRegistry::NAME_NL                                => $request->getNameNl(),

            ServiceRegistry::DESCRIPTION_EN                         => $request->getDescriptionEn(),
            ServiceRegistry::DESCRIPTION_NL                         => $request->getDescriptionNl(),
            ServiceRegistry::URL_NL                                 => $request->getApplicationUrl(),
            ServiceRegistry::URL_EN                                 => $request->getApplicationUrl(),
            ServiceRegistry::COIN_EULA                              => $request->getEulaUrl(),

            ServiceRegistry::NAMEIDFORMAT                           => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

            ServiceRegistry::LOGO_0_URL                             => $request->getLogoUrl(),
            ServiceRegistry::LOGO_0_WIDTH                           => $width,
            ServiceRegistry::LOGO_0_HEIGHT                          => $height,

            ServiceRegistry::CONTACTS_0_CONTACTTYPE                 => ServiceRegistry::CONTACT_TYPE_TECHNICAL,
            ServiceRegistry::CONTACTS_0_EMAILADDRESS                => $request->getTechnicalContact()->getEmail(),
            ServiceRegistry::CONTACTS_0_GIVENNAME                   => $request->getTechnicalContact()->getFirstName(),
            ServiceRegistry::CONTACTS_0_SURNAME                     => $request->getTechnicalContact()->getLastName(),
            ServiceRegistry::CONTACTS_0_TELEPHONENUMBER             => $request->getTechnicalContact()->getPhone(),

            ServiceRegistry::CONTACTS_1_CONTACTTYPE                 => ServiceRegistry::CONTACT_TYPE_SUPPORT,
            ServiceRegistry::CONTACTS_1_EMAILADDRESS                => $request->getSupportContact()->getEmail(),
            ServiceRegistry::CONTACTS_1_GIVENNAME                   => $request->getSupportContact()->getFirstName(),
            ServiceRegistry::CONTACTS_1_SURNAME                     => $request->getSupportContact()->getLastName(),
            ServiceRegistry::CONTACTS_1_TELEPHONENUMBER             => $request->getSupportContact()->getPhone(),

            ServiceRegistry::CONTACTS_2_CONTACTTYPE                 => ServiceRegistry::CONTACT_TYPE_ADMINISTRATIVE,
            ServiceRegistry::CONTACTS_2_EMAILADDRESS                => $request->getAdministrativeContact()->getEmail(),
            ServiceRegistry::CONTACTS_2_GIVENNAME                   => $request->getAdministrativeContact()->getFirstName(),
            ServiceRegistry::CONTACTS_2_SURNAME                     => $request->getAdministrativeContact()->getLastName(),
            ServiceRegistry::CONTACTS_2_TELEPHONENUMBER             => $request->getAdministrativeContact()->getPhone(),

            ServiceRegistry::ASSERTIONCONSUMERSERVICE_0_BINDING     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ServiceRegistry::ASSERTIONCONSUMERSERVICE_0_LOCATION    => $request->getAcsLocation(),
            ServiceRegistry::CERTDATA                               => $this->mapX509ToCertData($request->getCertificate()),
        );
    }

    private function mapX509ToCertData($certificate)
    {
        $lines = explode("\n", $certificate);
        $data = '';
        foreach ($lines as $line) {
            $line = rtrim($line);
            // Skip the header
            if ($line === self::PEM_HEADER) {
                continue;
            }
            // End transformation on footer
            if ($line === self::PEM_FOOTER) {
                break;
            }
            $data .= $line;
        }
        return $data;
    }

    private function getArpAttributesFromRequest($request)
    {
        $arp = array();
        $map = $this->getAttributeMap();
        foreach ($map as $property => $info) {
            $attr = $request->{'get' . ucfirst($property) . 'Attribute'}();

            if (!$attr instanceof Attribute) {
                continue;
            }

            if (!$attr->isRequested()) {
                continue;
            }

            $attributeMaceId = $info['name'][0];
            $arp[$attributeMaceId] = array('*');
        }

        if (empty($arp)) {
            return null;
        }

        return new ArpAttributes($arp);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getAttributeMap()
    {
        return array(
            'displayName'        => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:displayName',
                    'urn:oid:2.16.840.1.113730.3.1.241'
                ),
                'friendlyName' => 'Display name'
            ),
            'affiliation'        => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:eduPersonAffiliation',
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.1'
                ),
                'friendlyName' => 'Affiliation'
            ),
            'emailAddress'       => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:mail',
                    'urn:oid:0.9.2342.19200300.100.1.3'
                ),
                'friendlyName' => 'Email address'
            ),
            'commonName'         => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:cn',
                    'urn:oid:2.5.4.3'
                ),
                'friendlyName' => 'Common name'
            ),
            'organization'       => array(
                'name'         => array(
                    'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                    'urn:oid:1.3.6.1.4.1.25178.1.2.9'
                ),
                'friendlyName' => 'Organization'
            ),
            'organizationType'   => array(
                'name'         => array(
                    'urn:mace:terena.org:attribute-def:schacHomeOrganizationType ',
                    'urn:oid:1.3.6.1.4.1.25178.1.2.10'
                ),
                'friendlyName' => 'Organization Type'
            ),
            'surName'            => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:sn',
                    'urn:oid:2.5.4.4'
                ),
                'friendlyName' => 'Surname'
            ),
            'givenName'          => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:givenName',
                    'urn:oid:2.5.4.42'
                ),
                'friendlyName' => 'Given name'
            ),
            'entitlement'        => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:eduPersonEntitlement',
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.7'
                ),
                'friendlyName' => 'Entitlement'
            ),
            'uid'                => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:uid',
                    'urn:oid:0.9.2342.19200300.100.1.1'
                ),
                'friendlyName' => 'uid'
            ),
            'principleName'      => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:eduPersonPrincipalName',
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.6'
                ),
                'friendlyName' => 'PrincipalName'
            ),
            'preferredLanguage'  => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:preferredLanguage',
                    'urn:oid:2.16.840.1.113730.3.1.39'
                ),
                'friendlyName' => 'preferredLanguage'
            ),
            'organizationalUnit' => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:eduPersonOrgUnitDN',
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.4'
                ),
                'friendlyName' => 'organizationalUnit'
            ),
            'personalCode'       => array(
                'name'         => array(
                    'urn:mace:dir:attribute-def:schacPersonalUniqueCode',
                    'urn:oid:1.3.6.1.4.1.1466.155.121.1.15'
                ),
                'friendlyName' => 'Employee/student number'
            )
        );
    }

    private function getContactOfType($contactType, Connection $connection)
    {
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_CONTACTTYPE)) {
            if ($connection->getMetadata(ServiceRegistry::CONTACTS_0_CONTACTTYPE) === $contactType) {
                return $this->getContact0($connection);
            }
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_CONTACTTYPE)) {
            if ($connection->getMetadata(ServiceRegistry::CONTACTS_1_CONTACTTYPE) === $contactType) {
                return $this->getContact1($connection);
            }
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_CONTACTTYPE)) {
            if ($connection->getMetadata(ServiceRegistry::CONTACTS_2_CONTACTTYPE) === $contactType) {
                return $this->getContact2($connection);
            }
        }
        return NULL;
    }

    private function getContact0(Connection $connection)
    {
        $contact = new Contact();

        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_GIVENNAME)) {
            $contact->setFirstName($connection->getMetadata(ServiceRegistry::CONTACTS_0_GIVENNAME));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_SURNAME)) {
            $contact->setLastName($connection->getMetadata(ServiceRegistry::CONTACTS_0_SURNAME));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_EMAILADDRESS)) {
            $contact->setEmail($connection->getMetadata(ServiceRegistry::CONTACTS_0_EMAILADDRESS));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_TELEPHONENUMBER)) {
            $contact->setPhone($connection->getMetadata(ServiceRegistry::CONTACTS_0_TELEPHONENUMBER));
        }

        return $contact;
    }

    private function getContact1(Connection $connection)
    {
        $contact = new Contact();

        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_GIVENNAME)) {
            $contact->setFirstName($connection->getMetadata(ServiceRegistry::CONTACTS_1_GIVENNAME));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_SURNAME)) {
            $contact->setLastName($connection->getMetadata(ServiceRegistry::CONTACTS_1_SURNAME));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_EMAILADDRESS)) {
            $contact->setEmail($connection->getMetadata(ServiceRegistry::CONTACTS_1_EMAILADDRESS));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_TELEPHONENUMBER)) {
            $contact->setPhone($connection->getMetadata(ServiceRegistry::CONTACTS_1_TELEPHONENUMBER));
        }

        return $contact;
    }

    private function getContact2(Connection $connection)
    {
        $contact = new Contact();

        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_GIVENNAME)) {
            $contact->setFirstName($connection->getMetadata(ServiceRegistry::CONTACTS_2_GIVENNAME));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_SURNAME)) {
            $contact->setLastName($connection->getMetadata(ServiceRegistry::CONTACTS_2_SURNAME));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_EMAILADDRESS)) {
            $contact->setEmail($connection->getMetadata(ServiceRegistry::CONTACTS_2_EMAILADDRESS));
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_TELEPHONENUMBER)) {
            $contact->setPhone($connection->getMetadata(ServiceRegistry::CONTACTS_2_TELEPHONENUMBER));
        }

        return $contact;
    }
}
