<?php

use AppBundle\Entity\Subscription;
use AppBundle\Model\Attribute;
use OpenConext\JanusClient\ArpAttributes;
use OpenConext\JanusClient\ConnectionAccess;
use OpenConext\JanusClient\Entity\Connection;

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
    }

    private function getMetadataFromRequest(Subscription $request)
    {
        // @todo what happens on a 404.
        list($width, $height) = getimagesize($request->getLogoUrl());

        return array(
            'name:en'           => $request->getNameEn(),
            'name:nl'           => $request->getNameNl(),

            'description:en'    => $request->getDescriptionEn(),
            'description:nl'    => $request->getDescriptionNl(),

            'url:nl'            => $request->getApplicationUrl(),
            'url:en'            => $request->getApplicationUrl(),

            'NameIDFormat'      => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

            'logo:0:url'                    => $request->getLogoUrl(),
            'logo:0:width'                  => $width,
            'logo:0:height'                 => $height,

            'contacts:0:contactType'        => 'technical',
            'contacts:0:emailAddress'       => $request->getTechnicalContact()->getEmail(),
            'contacts:0:givenName'          => $request->getTechnicalContact()->getFirstName(),
            'contacts:0:surName'            => $request->getTechnicalContact()->getLastName(),
            'contacts:0:telephoneNumber'    => $request->getTechnicalContact()->getPhone(),

            'contacts:1:contactType'        => 'support',
            'contacts:1:emailAddress'       => $request->getSupportContact()->getEmail(),
            'contacts:1:givenName'          => $request->getSupportContact()->getFirstName(),
            'contacts:1:surName'            => $request->getSupportContact()->getLastName(),
            'contacts:1:telephoneNumber'    => $request->getSupportContact()->getPhone(),

            'contacts:2:contactType'        => 'administrative',
            'contacts:2:emailAddress'       => $request->getAdministrativeContact()->getEmail(),
            'contacts:2:givenName'          => $request->getAdministrativeContact()->getFirstName(),
            'contacts:2:surName'            => $request->getAdministrativeContact()->getLastName(),
            'contacts:2:telephoneNumber'    => $request->getAdministrativeContact()->getPhone(),

            'AssertionConsumerService:0:Binding'  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'AssertionConsumerService:0:Location' => $request->getAcsLocation(),
            'certData' => $this->mapX509ToCertData($request->getCertificate()),
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
}
