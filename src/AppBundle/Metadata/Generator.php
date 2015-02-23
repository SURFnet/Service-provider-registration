<?php

namespace AppBundle\Metadata;

use AppBundle\Entity\Subscription;
use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use Doctrine\Common\Cache\Cache;
use Monolog\Logger;

/**
 * Class Generator
 */
class Generator extends MetadataUtil
{
    /**
     * Constructor
     *
     * @param Fetcher $fetcher
     * @param Cache   $cache
     * @param Logger  $logger
     */
    public function __construct(Fetcher $fetcher, Cache $cache, Logger $logger)
    {
        $this->fetcher = $fetcher;

        parent::__construct($cache, $logger);
    }

    /**
     * @param Subscription $subscription
     *
     * @return string
     */
    public function generate(Subscription $subscription)
    {
        $cacheId = 'generated-xml-' . $subscription->getId();
        if (false !== ($xml = $this->cache->fetch($cacheId))) {
            return $xml;
        }

        $xml = $this->fetcher->fetch($subscription->getMetadataUrl());
        $xml = simplexml_load_string($xml);

        $children = $xml->children(self::NS_SAML);
        /** @var \SimpleXMLElement $descriptor */
        $descriptor = $children->SPSSODescriptor;

        $this->generateUi($descriptor, $subscription);
        $this->generateContacts($xml, $subscription);
        $this->generateAttributes($descriptor, $subscription);

        $xml = $xml->asXML();

        $this->cache->save($cacheId, $xml);

        return $xml;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param Subscription      $subscription
     */
    private function generateUi(\SimpleXMLElement $xml, Subscription $subscription)
    {
        $extensions = $this->setNode($xml, 'md:Extensions', null, array(), array('md' => self::NS_SAML));
        $ui = $this->setNode($extensions, 'ui:UIInfo', null, array(), array('ui' => self::NS_UI));

        $this->setNode(
            $ui,
            'ui:Logo',
            $subscription->getLogoUrl(),
            array(),
            array('ui' => self::NS_UI)
        );

        $this->setNode(
            $ui,
            'ui:Description',
            $subscription->getDescriptionEn(),
            array('xml:lang' => 'en'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );

        $this->setNode(
            $ui,
            'ui:Description',
            $subscription->getDescriptionNl(),
            array('xml:lang' => 'nl'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );

        $this->setNode(
            $ui,
            'ui:DisplayName',
            $subscription->getNameEn(),
            array('xml:lang' => 'en'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );

        $this->setNode(
            $ui,
            'ui:DisplayName',
            $subscription->getNameNl(),
            array('xml:lang' => 'nl'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );

        $this->setNode(
            $ui,
            'ui:InformationURL',
            $subscription->getApplicationUrl(),
            array('xml:lang' => 'en'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param Subscription      $subscription
     */
    private function generateContacts(\SimpleXMLElement $xml, Subscription $subscription)
    {
        if ($subscription->getSupportContact() instanceof Contact) {
            $this->generateContact($xml, $subscription->getSupportContact(), 'support');
        }

        if ($subscription->getTechnicalContact() instanceof Contact) {
            $this->generateContact($xml, $subscription->getTechnicalContact(), 'technical');
        }

        if ($subscription->getAdministrativeContact() instanceof Contact) {
            $this->generateContact($xml, $subscription->getAdministrativeContact(), 'administrative');
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param Contact           $contact
     * @param                   $type
     */
    private function generateContact(\SimpleXMLElement $xml, Contact $contact, $type)
    {
        $node = $this->setNode(
            $xml,
            'md:ContactPerson',
            '',
            array('contactType' => $type),
            array('md' => self::NS_SAML)
        );

        $this->setNode($node, 'md:GivenName', $contact->getFirstName(), array(), array('md' => self::NS_SAML));
        $this->setNode($node, 'md:SurName', $contact->getLastName(), array(), array('md' => self::NS_SAML));
        $this->setNode($node, 'md:EmailAddress', $contact->getEmail(), array(), array('md' => self::NS_SAML));
        $this->setNode($node, 'md:TelephoneNumber', $contact->getPhone(), array(), array('md' => self::NS_SAML));
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param Subscription      $subscription
     */
    private function generateAttributes(\SimpleXMLElement $xml, Subscription $subscription)
    {
        $node = $this->setNode($xml, 'md:AttributeConsumingService', null, array('index' => 0), array('md' => self::NS_SAML));

        foreach ($this->getAttributeMap() as $property => $attributes) {
            $attr = $subscription->{'get' . ucfirst($property) . 'Attribute'}();

            if ($attr instanceof Attribute && $attr->isRequested()) {
                $this->generateAttribute($node, $attributes['name'], $attributes['friendlyName']);
            } else {
                $this->removeAttribute($node, $attributes['name']);
            }
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param array             $names
     * @param string            $friendlyName
     */
    private function generateAttribute(\SimpleXMLElement $xml, array $names, $friendlyName)
    {
        // First try to find an existing node
        foreach ($names as $name) {
            $node = $this->findNode(
                $xml,
                'md:RequestedAttribute',
                array('Name' => $name),
                array('md' => self::NS_SAML)
            );

            if ($node !== null) {
                $node['FriendlyName'] = $friendlyName;

                return;
            }
        }

        // If no existing node has been found, create and set one with the first name from the supplied names
        $this->setNode(
            $xml,
            'md:RequestedAttribute',
            null,
            array('Name' => $names[0], 'FriendlyName' => $friendlyName),
            array('md' => self::NS_SAML)
        );
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param array             $names
     */
    private function removeAttribute(\SimpleXMLElement $xml, array $names)
    {
        foreach ($names as $name) {
            $node = $this->findNode(
                $xml,
                'md:RequestedAttribute',
                array('Name' => $name),
                array('md' => self::NS_SAML)
            );

            if ($node !== null) {
                unset($node[0]);
            }
        }
    }

    /**
     * @param \SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param string            $value
     * @param array             $attributes
     * @param array             $cnss
     * @param array             $anss
     *
     * @return \SimpleXMLElement
     */
    private function setNode(
        \SimpleXMLElement $rootNode,
        $nodeName,
        $value = null,
        $attributes = array(),
        $cnss = array(),
        $anss = array()
    ) {
        $node = $this->findNode($rootNode, $nodeName, $attributes, array_merge($cnss, $anss));

        if (isset($node)) {
            if ($value !== null) {
                $node[0] = $value;
            }

            return $node;
        }

        $ns = count($cnss) > 0 ? reset($cnss) : null;
        $node = $rootNode->addChild($nodeName, $value, $ns);

        foreach ($attributes as $aName => $aValue) {
            $ns = count($anss) > 0 ? reset($anss) : null;
            $node->addAttribute($aName, $aValue, $ns);
        }

        return $node;
    }

    /**
     * @param \SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param array             $attributes
     * @param array             $nss
     *
     * @return null|\SimpleXMLElement
     */
    private function findNode(
        \SimpleXMLElement $rootNode,
        $nodeName,
        $attributes = array(),
        $nss = array()
    ) {
        $xpathExpression = './' . $nodeName;

        foreach ($attributes as $aName => $aValue) {
            $xpathExpression .= '[@' . $aName . '=\'' . $aValue . '\']';
        }

        foreach ($nss as $alias => $ns) {
            $rootNode->registerXPathNamespace($alias, $ns);
        }

        $node = $rootNode->xpath($xpathExpression);

        if (isset($node[0][0])) {
            return $node[0];
        }

        return null;
    }
}
