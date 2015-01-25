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
}
