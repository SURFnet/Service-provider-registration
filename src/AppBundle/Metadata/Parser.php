<?php

namespace AppBundle\Metadata;

use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
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
    const NS_UI = 'urn:oasis:names:tc:SAML:metadata:ui';
    const NS_LANG = 'http://www.w3.org/XML/1998/namespace';

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

        $children = $responseXml->children(self::NS_SAML);
        $descriptor = $children->SPSSODescriptor;
        $contactPersons = $children->ContactPerson;

        if (!isset($descriptor->AssertionConsumerService)) {
            throw new \InvalidArgumentException('Invalid metadata XML');
        }

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

        if (isset($descriptor->KeyDescriptor)) {
            $this->parseCertificate($descriptor, $metadata);
        }

        if (isset($descriptor->Extensions)) {
            $this->parseUi($descriptor, $metadata);
        }

        $this->parseContactPersons($contactPersons, $metadata);

        if (isset($descriptor->AttributeConsumingService)) {
            $this->parseAttributes($descriptor, $metadata);
        }

        return $this->cache[$metadataUrl] = $metadata;
    }

    /**
     * @param \SimpleXMLElement $descriptor
     * @param Metadata          $metadata
     */
    private function parseCertificate($descriptor, Metadata $metadata)
    {
        foreach ($descriptor->KeyDescriptor->children(self::NS_SIG) as $keyInfo) {
            $metadata->certificate = "-----BEGIN CERTIFICATE-----\n";
            $metadata->certificate .= trim((string)$keyInfo->X509Data->X509Certificate);
            $metadata->certificate .= "\n-----END CERTIFICATE-----";
            break;
        }
    }

    /**
     * @param \SimpleXMLElement $descriptor
     * @param Metadata          $metadata
     */
    private function parseUi($descriptor, Metadata $metadata)
    {
        $ui = $descriptor->Extensions->children(self::NS_UI)->UIInfo;

        $metadata->logoUrl = (string)$ui->Logo;

        foreach ($ui->Description as $description) {
            $lang = $description->attributes(self::NS_LANG);
            $lang = $lang['lang'];

            switch ($lang) {
                case 'en':
                    $metadata->descriptionEn = (string)$description;
                    break;

                case 'nl':
                    $metadata->descriptionNl = (string)$description;
                    break;
            }
        }

        foreach ($ui->DisplayName as $name) {
            $lang = $name->attributes(self::NS_LANG);
            $lang = $lang['lang'];

            switch ($lang) {
                case 'en':
                    $metadata->nameEn = (string)$name;
                    break;

                case 'nl':
                    $metadata->nameNl = (string)$name;
                    break;
            }
        }

        foreach ($ui->InformationURL as $url) {
            $lang = $url->attributes(self::NS_LANG);
            $lang = $lang['lang'];

            switch ($lang) {
                case 'en':
                    $metadata->applicationUrlEn = (string)$url;
                    break;

                case 'nl':
                    $metadata->applicationUrlNl = (string)$url;
                    break;
            }
        }
    }

    /**
     * @param \SimpleXMLElement $persons
     * @param Metadata          $metadata
     */
    private function parseContactPersons($persons, Metadata $metadata)
    {
        foreach ($persons as $person) {

            $contact = new Contact();
            $contact->setFirstName((string)$person->GivenName);
            $contact->setLastName((string)$person->SurName);
            $contact->setEmail((string)$person->EmailAddress);
            $contact->setPhone((string)$person->TelephoneNumber);

            $type = $person->attributes();
            switch ($type['contactType']) {
                case 'support':
                    $metadata->supportContact = $contact;
                    break;

                case 'technical':
                    $metadata->technicalContact = $contact;
                    break;

                case 'administrative':
                    $metadata->administrativeContact = $contact;
                    break;
            }
        }
    }

    /**
     * @param \SimpleXMLElement $descriptor
     * @param Metadata          $metadata
     */
    private function parseAttributes($descriptor, Metadata $metadata)
    {
        foreach ($descriptor->AttributeConsumingService->RequestedAttribute as $attribute) {

            $attr = new Attribute();
            $attr->setRequested(true);

            $attributes = $attribute->attributes();

            switch ($attributes['Name']) {
                case 'urn:mace:dir:attribute-def:displayName':
                    $metadata->displayNameAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:eduPersonAffiliation':
                    $metadata->affiliationAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:mail':
                    $metadata->emailAddressAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:cn':
                    $metadata->commonNameAttribute = $attr;
                    break;

                // @todo: Unknown?
                case 'urn:mace:dir:attribute-def:schacHomeOrganization':
                case 'urn:mace:terena.org:attribute-def:schacHomeOrganization':
                    break;

                case 'urn:mace:dir:attribute-def:sn':
                    $metadata->surNameAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:givenName':
                    $metadata->givenNameAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:uid':
                    $metadata->uidAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:eduPersonPrincipalName':
                    $metadata->principleNameAttribute = $attr;
                    break;

                /**
                 * @todo: Unknown?
                 * urn:oid:0.9.2342.19200300.100.1.3 //mail
                 * urn:oid:2.16.840.1.113730.3.1.241 //displayName
                 * urn:oid:2.5.4.3 //commonName
                 * urn:oid:1.3.6.1.4.1.25178.1.2.9 //
                 * urn:oid:1.3.6.1.4.1.5923.1.1.1.1
                 * urn:oid:1.3.6.1.4.1.5923.1.1.1.6
                 */
            }
        }
    }

    /**
     * @param string $xml
     *
     * @todo: fix me
     */
    private function validate($xml)
    {
        return;

        $opts = array(
            'http' => array(
                'user_agent' => 'PHP libxml agent',
            )
        );
        $context = stream_context_create($opts);
        libxml_set_streams_context($context);

        $doc = new \DOMDocument();
        $doc->loadXml($xml);

        if (!$doc->schemaValidate(self::XSD_SAML_METADATA)) {
            throw new \InvalidArgumentException('The metadata XML is invalid considering the associated XSD.');
        }
    }
}
