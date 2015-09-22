<?php

namespace SURFnet\SPRegistration\Janus;

use AppBundle\Entity\Subscription;
use AppBundle\Model\Attribute;
use OpenConext\JanusClient\ArpAttributes;

/**
 * Class ArpMapper
 * @package SURFnet\SPRegistration\Janus
 */
final class ArpMapper
{
    /**
     * @param Subscription $request
     * @return null|ArpAttributes
     */
    public function mapRequestToArpAttributes(Subscription $request)
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
            'displayName' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:displayName',
                    'urn:oid:2.16.840.1.113730.3.1.241'
                ),
                'friendlyName' => 'Display name'
            ),
            'affiliation' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:eduPersonAffiliation',
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.1'
                ),
                'friendlyName' => 'Affiliation'
            ),
            'emailAddress' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:mail',
                    'urn:oid:0.9.2342.19200300.100.1.3'
                ),
                'friendlyName' => 'Email address'
            ),
            'commonName' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:cn',
                    'urn:oid:2.5.4.3'
                ),
                'friendlyName' => 'Common name'
            ),
            'organization' => array(
                'name' => array(
                    'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                    'urn:oid:1.3.6.1.4.1.25178.1.2.9'
                ),
                'friendlyName' => 'Organization'
            ),
            'organizationType' => array(
                'name' => array(
                    'urn:mace:terena.org:attribute-def:schacHomeOrganizationType ',
                    'urn:oid:1.3.6.1.4.1.25178.1.2.10'
                ),
                'friendlyName' => 'Organization Type'
            ),
            'surName' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:sn',
                    'urn:oid:2.5.4.4'
                ),
                'friendlyName' => 'Surname'
            ),
            'givenName' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:givenName',
                    'urn:oid:2.5.4.42'
                ),
                'friendlyName' => 'Given name'
            ),
            'entitlement' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:eduPersonEntitlement',
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.7'
                ),
                'friendlyName' => 'Entitlement'
            ),
            'uid' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:uid',
                    'urn:oid:0.9.2342.19200300.100.1.1'
                ),
                'friendlyName' => 'uid'
            ),
            'principleName' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:eduPersonPrincipalName',
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.6'
                ),
                'friendlyName' => 'PrincipalName'
            ),
            'preferredLanguage' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:preferredLanguage',
                    'urn:oid:2.16.840.1.113730.3.1.39'
                ),
                'friendlyName' => 'preferredLanguage'
            ),
            'organizationalUnit' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:eduPersonOrgUnitDN',
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.4'
                ),
                'friendlyName' => 'organizationalUnit'
            ),
            'personalCode' => array(
                'name' => array(
                    'urn:mace:dir:attribute-def:schacPersonalUniqueCode',
                    'urn:oid:1.3.6.1.4.1.1466.155.121.1.15'
                ),
                'friendlyName' => 'Employee/student number'
            )
        );
    }
}
