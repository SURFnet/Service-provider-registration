<?php

namespace AppBundle\Metadata;

use AppBundle\Entity\Subscription;
use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use Doctrine\Common\Cache\Cache;
use Monolog\Logger;

/**
 * Class Generator
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
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

        $this->cache->save($cacheId, $xml, 60 * 60);

        return $xml;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param Subscription      $subscription
     */
    private function generateUi(\SimpleXMLElement $xml, Subscription $subscription)
    {
        $extensions = $this->setNode($xml, 'md:Extensions', null, array(), array('md' => self::NS_SAML), array(), 0);
        $ui = $this->setNode($extensions, 'ui:UIInfo', null, array(), array('ui' => self::NS_UI));

        $this->generateLogo($ui, $subscription);

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
    private function generateLogo(\SimpleXMLElement $xml, Subscription $subscription)
    {
        $logo = $subscription->getLogoUrl();
        if (empty($logo)) {
            $this->removeNode($xml, 'ui:Logo', array(), array('ui' => self::NS_UI));

            return;
        }

        $node = $this->setNode(
            $xml,
            'ui:Logo',
            $logo,
            array(),
            array('ui' => self::NS_UI)
        );

        $logoData = @getimagesize($logo);

        if ($logoData !== false) {
            list($width, $height) = $logoData;

            $node['width'] = $width;
            $node['height'] = $height;
        }
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
        $node = $this->setNode(
            $xml,
            'md:AttributeConsumingService',
            null,
            array('index' => 0),
            array('md' => self::NS_SAML)
        );

        if (null === $this->findNode($node, 'md:ServiceName', array(), array('md' => self::NS_SAML))) {
            $this->setNode(
                $node,
                'md:ServiceName',
                $subscription->getNameEn(),
                array('xml:lang' => 'en'),
                array('md' => self::NS_SAML),
                array('xml' => self::NS_LANG),
                0
            );
        }

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
     * Update (or Add if it not exists) a child node with the specified value
     *
     * @param \SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param string            $value
     * @param array             $attributes
     * @param array             $cnss     child namespaces
     * @param array             $anss     attribute namespaces
     * @param null              $position to add the element, if null, it will be appended to rootNode
     *
     * @return \SimpleXMLElement
     */
    private function setNode(
        \SimpleXMLElement $rootNode,
        $nodeName,
        $value = null,
        $attributes = array(),
        $cnss = array(),
        $anss = array(),
        $position = null
    ) {
        $node = $this->findNode($rootNode, $nodeName, $attributes, array_merge($cnss, $anss));

        if (isset($node)) {
            if ($value !== null) {
                $node[0] = $value;
            }

            return $node;
        }

        return $this->addNode($rootNode, $nodeName, $value, $attributes, $cnss, $anss, $position);
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

    /**
     * @param \SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param null              $value
     * @param array             $attributes
     * @param array             $cnss
     * @param array             $anss
     * @param null              $position
     *
     * @return \SimpleXMLElement
     */
    private function addNode(
        \SimpleXMLElement $rootNode,
        $nodeName,
        $value = null,
        $attributes = array(),
        $cnss = array(),
        $anss = array(),
        $position = null
    ) {
        $ns = count($cnss) > 0 ? reset($cnss) : null;
        $node = $this->addChildNodeAt($rootNode, $nodeName, $value, $ns, $position);

        foreach ($attributes as $aName => $aValue) {
            $ns = count($anss) > 0 ? reset($anss) : null;
            $node->setAttributeNS($ns, $aName, $aValue);
        }

        return simplexml_import_dom($node);
    }

    /**
     * @param \SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param array             $attributes
     * @param array             $cnss
     * @param array             $anss
     */
    private function removeNode(
        \SimpleXMLElement $rootNode,
        $nodeName,
        $attributes = array(),
        $cnss = array(),
        $anss = array()
    ) {
        $node = $this->findNode($rootNode, $nodeName, $attributes, array_merge($cnss, $anss));

        if ($node !== null) {
            unset($node[0]);
        }
    }

    /**
     * @param \SimpleXMLElement $parent
     * @param string            $nodeName
     * @param string            $value
     * @param string            $ns
     * @param int               $position
     *
     * @return \DOMElement
     */
    private function addChildNodeAt(\SimpleXMLElement $parent, $nodeName, $value = null, $ns = null, $position = null)
    {
        $parent = dom_import_simplexml($parent);

        $child = new \DOMElement($nodeName, $value, $ns);
        $child = $parent->ownerDocument->importNode($child, true);

        if ($position === null || $parent->childNodes->item($position) === null) {
            return $parent->appendChild($child);
        } else {
            return $parent->insertBefore($child, $parent->childNodes->item($position));
        }
    }
}
