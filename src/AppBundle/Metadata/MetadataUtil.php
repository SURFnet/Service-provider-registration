<?php

namespace AppBundle\Metadata;

use Doctrine\Common\Cache\Cache;
use Monolog\Logger;

/**
 * Class MetadataUtil
 */
abstract class MetadataUtil
{
    const NS_SAML = 'urn:oasis:names:tc:SAML:2.0:metadata';
    const NS_SIG = 'http://www.w3.org/2000/09/xmldsig#';
    const NS_UI = 'urn:oasis:names:tc:SAML:metadata:ui';
    const NS_LANG = 'http://www.w3.org/XML/1998/namespace';

    const ATTR_ACS_POST_BINDING = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
    const XSD_SAML_METADATA = 'saml-schema-metadata-2.0.xsd';

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Cache  $cache
     * @param Logger $logger
     */
    public function __construct(Cache $cache, Logger $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param string $message
     * @param mixed  $context
     */
    protected function log($message, $context)
    {
        if (!$this->logger instanceof Logger) {
            return;
        }

        $this->logger->addInfo($message, array('context' => $context));
    }

    /**
     * @return array
     */
    protected function getAttributeMap()
    {
        return array(
            'displayName' => array(
                'urn:mace:dir:attribute-def:displayName',
                'urn:oid:2.16.840.1.113730.3.1.241'
            ),
            'affiliation' => array(
                'urn:mace:dir:attribute-def:eduPersonAffiliation',
                'urn:oid:1.3.6.1.4.1.5923.1.1.1.1'
            ),
            'emailAddress' => array(
                'urn:mace:dir:attribute-def:mail',
                'urn:oid:0.9.2342.19200300.100.1.3'
            ),
            'commonName' => array(
                'urn:mace:dir:attribute-def:cn',
                'urn:oid:2.5.4.3'
            ),
            'organization' => array(
                'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                'urn:oid:1.3.6.1.4.1.25178.1.2.9'
            ),
            'organizationType' => array(
                'urn:mace:terena.org:attribute-def:schacHomeOrganizationType ',
                'urn:oid:1.3.6.1.4.1.25178.1.2.10'
            ),
            'surName' => array(
                'urn:mace:dir:attribute-def:sn',
                'urn:oid:2.5.4.4'
            ),
            'givenName' => array(
                'urn:mace:dir:attribute-def:givenName',
                'urn:oid:2.5.4.42'
            ),
            'entitlement' => array(
                'urn:mace:dir:attribute-def:eduPersonEntitlement',
                'urn:oid:1.3.6.1.4.1.5923.1.1.1.7'
            ),
            'uid' => array(
                'urn:mace:dir:attribute-def:uid',
                'urn:oid:0.9.2342.19200300.100.1.1'
            ),
            'principleName' => array(
                'urn:mace:dir:attribute-def:eduPersonPrincipalName',
                'urn:oid:1.3.6.1.4.1.5923.1.1.1.6'
            ),
            'preferredLanguage' => array(
                'urn:mace:dir:attribute-def:preferredLanguage',
                'urn:oid:2.16.840.1.113730.3.1.39'
            ),
            'organizationalUnit' => array(
                'urn:mace:dir:attribute-def:eduPersonOrgUnitDN',
                'urn:oid:1.3.6.1.4.1.5923.1.1.1.4'
            ),
            'personalCode' => array(
                'urn:mace:dir:attribute-def:schacPersonalUniqueCode',
                'urn:oid:1.3.6.1.4.1.1466.155.121.1.15'
            )
        );
    }
}
