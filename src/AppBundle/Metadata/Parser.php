<?php

namespace AppBundle\Metadata;

use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use AppBundle\Model\Metadata;
use Doctrine\Common\Cache\Cache;
use Monolog\Logger;

/**
 * Class Parser
 *
 * @todo: this class could use some refactoring
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Parser extends MetadataUtil
{
    /**
     * @var Fetcher
     */
    private $fetcher;

    /**
     * @var CertificateParser
     */
    private $certParser;

    /**
     * @var string
     */
    private $schemaLocation;

    /**
     * Constructor
     *
     * @param Fetcher           $fetcher
     * @param CertificateParser $certParser
     * @param string            $schemaLocation
     * @param string            $schemaLocation
     * @param Cache             $cache
     * @param Logger            $logger
     */
    public function __construct(
        Fetcher $fetcher,
        CertificateParser $certParser,
        $schemaLocation,
        Cache $cache,
        Logger $logger
    ) {
        $this->fetcher = $fetcher;
        $this->certParser = $certParser;
        $this->schemaLocation = $schemaLocation;

        parent::__construct($cache, $logger);
    }

    /**
     * @param string $metadataUrl
     *
     * @return Metadata
     */
    public function parse($metadataUrl)
    {
        if (false !== $metadata = $this->cache->fetch('metadata-' . $metadataUrl)) {
            return $metadata;
        }

        $responseXml = $this->fetcher->fetch($metadataUrl);

        $this->validate($responseXml);

        $responseXml = simplexml_load_string($responseXml);

        $metadata = new Metadata();
        $metadata->entityId = (string)$responseXml['entityID'];

        $children = $responseXml->children(self::NS_SAML);
        $descriptor = $children->SPSSODescriptor;
        $contactPersons = $children->ContactPerson;

        $this->parseAssertionConsumerService($descriptor, $metadata);

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

        $this->cache->save('metadata-' . $metadataUrl, $metadata, 60 * 60 * 24);

        return $metadata;
    }

    /**
     * @param  \SimpleXMLElement $descriptor
     * @param Metadata           $metadata
     */
    private function parseAssertionConsumerService($descriptor, Metadata $metadata)
    {
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
    }

    /**
     * @param \SimpleXMLElement $descriptor
     * @param Metadata          $metadata
     */
    private function parseCertificate($descriptor, Metadata $metadata)
    {
        foreach ($descriptor->KeyDescriptor->children(self::NS_SIG) as $keyInfo) {
            $metadata->certificate = $this->certParser->parse((string)$keyInfo->X509Data->X509Certificate);
            break;
        }
    }

    /**
     * @param \SimpleXMLElement $descriptor
     * @param Metadata          $metadata
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function parseAttributes($descriptor, Metadata $metadata)
    {
        foreach ($descriptor->AttributeConsumingService->RequestedAttribute as $attribute) {

            $attr = new Attribute();
            $attr->setRequested(true);

            $attributes = $attribute->attributes();

            switch ($attributes['Name']) {
                case 'urn:mace:dir:attribute-def:displayName':
                case 'urn:oid:2.16.840.1.113730.3.1.241':
                    $metadata->displayNameAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:eduPersonAffiliation':
                case 'urn:oid:1.3.6.1.4.1.5923.1.1.1.1':
                    $metadata->affiliationAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:mail':
                case 'urn:oid:0.9.2342.19200300.100.1.3':
                    $metadata->emailAddressAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:cn':
                case 'urn:oid:2.5.4.3':
                    $metadata->commonNameAttribute = $attr;
                    break;

                case 'urn:mace:terena.org:attribute-def:schacHomeOrganization':
                case 'urn:oid:1.3.6.1.4.1.25178.1.2.9':
                    $metadata->organizationAttribute = $attr;
                    break;

                case 'urn:mace:terena.org:attribute-def:schacHomeOrganizationType ':
                case 'urn:oid:1.3.6.1.4.1.25178.1.2.10':
                    $metadata->organizationTypeAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:sn':
                case 'urn:oid:2.5.4.4':
                    $metadata->surNameAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:givenName':
                case 'urn:oid:2.5.4.42':
                    $metadata->givenNameAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:eduPersonEntitlement':
                case 'urn:oid:1.3.6.1.4.1.5923.1.1.1.7':
                    $metadata->entitlementAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:uid':
                case 'urn:oid:0.9.2342.19200300.100.1.1':
                    $metadata->uidAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:eduPersonPrincipalName':
                case 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6':
                    $metadata->principleNameAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:preferredLanguage':
                case 'urn:oid:2.16.840.1.113730.3.1.39':
                    $metadata->preferredLanguageAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:eduPersonOrgUnitDN':
                case 'urn:oid:1.3.6.1.4.1.5923.1.1.1.4':
                    $metadata->organizationalUnitAttribute = $attr;
                    break;

                case 'urn:mace:dir:attribute-def:schacPersonalUniqueCode':
                case 'urn:oid:1.3.6.1.4.1.1466.155.121.1.15':
                    $metadata->personalCodeAttribute = $attr;
                    break;
            }
        }
    }

    /**
     * @param string $xml
     */
    private function validate($xml)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadXml($xml);

        if (!$doc->schemaValidate($this->schemaLocation . self::XSD_SAML_METADATA)) {
            $this->log('Metadata XML validation errors', libxml_get_errors());
            libxml_clear_errors();
            throw new \InvalidArgumentException('The metadata XML is invalid considering the associated XSD.');
        }
    }
}
