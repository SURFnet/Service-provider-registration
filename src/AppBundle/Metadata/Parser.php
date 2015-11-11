<?php

namespace AppBundle\Metadata;

use AppBundle\Metadata\Exception\ParserException;
use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use AppBundle\Model\Metadata;
use Monolog\Logger;
use SAML2_Const;
use SAML2_XML_mdui_UIInfo;
use XMLSecurityDSig;

/**
 * Class Parser
 *
 * @todo: this class could use some refactoring
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Parser
{
    const NS_LANG = 'http://www.w3.org/XML/1998/namespace';

    /**
     * @var Fetcher
     */
    private $fetcher;

    /**
     * @var CertificateParser
     */
    private $certParser;

    /**
     * @var AttributesMetadataRepository
     */
    private $attributesMetadataRepository;

    /**
     * @var string
     */
    private $schemaLocation;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Fetcher                      $fetcher
     * @param CertificateParser            $certParser
     * @param AttributesMetadataRepository $attributesMetadataRepository
     * @param string                       $schemaLocation
     * @param Logger                       $logger
     */
    public function __construct(
        Fetcher $fetcher,
        CertificateParser $certParser,
        AttributesMetadataRepository $attributesMetadataRepository,
        $schemaLocation,
        Logger $logger
    ) {
        $this->fetcher = $fetcher;
        $this->certParser = $certParser;
        $this->attributesMetadataRepository = $attributesMetadataRepository;
        $this->schemaLocation = $schemaLocation;
        $this->logger = $logger;
    }

    /**
     * @param string $metadataUrl
     *
     * @return Metadata
     */
    public function parse($metadataUrl)
    {
        $responseXml = $this->fetcher->fetch($metadataUrl);

        $this->validate($responseXml);

        $responseXml = simplexml_load_string($responseXml);

        $metadata = new Metadata();
        $metadata->entityId = (string)$responseXml['entityID'];

        $children = $responseXml->children(SAML2_Const::NS_MD);
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

            if ((string)$acs['Binding'] === SAML2_Const::BINDING_HTTP_POST) {
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
        foreach ($descriptor->KeyDescriptor->children(XMLSecurityDSig::XMLDSIGNS) as $keyInfo) {
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
        $ui = $descriptor->Extensions->children(SAML2_XML_mdui_UIInfo::NS)->UIInfo;

        $metadata->logoUrl = (string)$ui->Logo;

        foreach ($ui->Description as $description) {
            $lang = $description->attributes(static::NS_LANG);
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
            $lang = $name->attributes(static::NS_LANG);
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
            $lang = $url->attributes(static::NS_LANG);
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
            $attr->setMotivation('');

            $attributes = $attribute->attributes();

            foreach ($this->attributesMetadataRepository->findAll() as $attributeMetadata) {
                if (in_array($attributes['Name'], $attributeMetadata->urns)) {
                    $metadata->{$attributeMetadata->id . 'Attribute'} = $attr;
                }
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

        if (!$doc->schemaValidate($this->schemaLocation . 'surf.xsd')) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            $this->logger->addInfo('Metadata XML validation errors:', $errors);

            $ex = new ParserException('The metadata XML is invalid considering the associated XSD');
            $ex->setParserErrors($errors);
            throw $ex;
        }
    }
}
