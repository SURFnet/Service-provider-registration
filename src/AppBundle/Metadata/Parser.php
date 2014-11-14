<?php

namespace AppBundle\Metadata;

use AppBundle\Model\Metadata;
use Guzzle\Common\Exception\GuzzleException;
use Guzzle\Http\Client;

/**
 * Class Parser
 */
class Parser
{
    const NS_SAML = 'urn:oasis:names:tc:SAML:2.0:metadata';
    const NS_SIG = 'http://www.w3.org/2000/09/xmldsig#';
    const ATTR_ACS_POST_BINDING = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
    const XSD_SAML_METADATA = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-metadata-2.0.xsd';

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * @var Metadata[]
     */
    private $cache;

    /**
     * @param $guzzle
     */
    public function __construct($guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @param string $metadataUrl
     *
     * @return Metadata
     */
    public function parse($metadataUrl)
    {
        if (isset($this->cache[$metadataUrl]) && $this->cache[$metadataUrl] instanceof Metadata) {
            return $this->cache[$metadataUrl];
        }

        try {
            $responseXml = $this->guzzle->get($metadataUrl, null, array('timeout' => 10))->send()->xml();
        } catch (GuzzleException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        $this->validate($responseXml->asXML());

        $metadata = new Metadata();
        $metadata->entityId = (string)$responseXml['entityID'];

        $descriptor = $responseXml->children(self::NS_SAML)->SPSSODescriptor;

        foreach ($descriptor->AssertionConsumerService as $acs) {
            $acs = $acs->attributes();

            if ((string)$acs['Binding'] === self::ATTR_ACS_POST_BINDING) {
                $metadata->acsLocation = (string)$acs['Location'];
            }

            if ((int)$acs['index'] > 9) {
                throw new \InvalidArgumentException(
                    'The metadata should not contain an ACS with an index larger than 9.'
                );
            }
        }

        foreach ($descriptor->KeyDescriptor->children(self::NS_SIG) as $keyDescriptor) {
            $metadata->certificate = "-----BEGIN CERTIFICATE-----\n";
            $metadata->certificate .= (string)$keyDescriptor->X509Data->X509Certificate;
            $metadata->certificate .= "\n-----END CERTIFICATE-----";
            break;
        }

        return $this->cache[$metadataUrl] = $metadata;
    }

    /**
     * @param string $xml
     *
     * @todo: fix me
     */
    private function validate($xml)
    {
        return;

        $doc = new \DOMDocument();
        $doc->loadXml($xml);

        if (!$doc->schemaValidateSource(self::XSD_SAML_METADATA)) {
            throw new \InvalidArgumentException('The metadata XML is invalid considering the associated XSD.');
        }
    }
}
