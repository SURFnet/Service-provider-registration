<?php

namespace AppBundle\Metadata;

use AppBundle\Entity\Subscription;
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

        $responseXml = $this->fetcher->fetch($subscription->getMetadataUrl());

        $responseXml = simplexml_load_string($responseXml);

        $children = $responseXml->children(self::NS_SAML);
        $descriptor = $children->SPSSODescriptor;
        /** @var \SimpleXMLElement $ui */
        $ui = $descriptor->Extensions->children(self::NS_UI)->UIInfo;

        $this->setNode($ui, 'ui:Logo', $subscription->getLogoUrl(), array(), array('ui' => self::NS_UI));
        $this->setNode($ui, 'ui:Description', $subscription->getDescriptionEn(), array('xml:lang' => 'en'), array('ui' => self::NS_UI));
        $this->setNode($ui, 'ui:Description', $subscription->getDescriptionNl(), array('xml:lang' => 'nl'), array('ui' => self::NS_UI));
        $this->setNode($ui, 'ui:DisplayName', $subscription->getNameEn(), array('xml:lang' => 'en'), array('ui' => self::NS_UI));
        $this->setNode($ui, 'ui:DisplayName', $subscription->getNameNl(), array('xml:lang' => 'nl'), array('ui' => self::NS_UI));
        $this->setNode($ui, 'ui:InformationURL', $subscription->getApplicationUrl(), array('xml:lang' => 'en'), array('ui' => self::NS_UI));

        if ($subscription->getSupportContact() instanceof Contact) {
            $node = $this->setNode($responseXml, 'md:ContactPerson', '', array('contactType' => 'support'), array('md' => self::NS_SAML));
            $this->setNode($node, 'md:GivenName', $subscription->getSupportContact()->getFirstName(), array(), array('md' => self::NS_SAML));
            $this->setNode($node, 'md:SurName', $subscription->getSupportContact()->getLastName(), array(), array('md' => self::NS_SAML));
            $this->setNode($node, 'md:EmailAddress', $subscription->getSupportContact()->getEmail(), array(), array('md' => self::NS_SAML));
            $this->setNode($node, 'md:TelephoneNumber', $subscription->getSupportContact()->getPhone(), array(), array('md' => self::NS_SAML));
        }

        if ($subscription->getTechnicalContact() instanceof Contact) {
            $node = $this->setNode($responseXml, 'md:ContactPerson', '', array('contactType' => 'technical'));
            $this->setNode($node, 'md:GivenName', $subscription->getTechnicalContact()->getFirstName());
            $this->setNode($node, 'md:SurName', $subscription->getTechnicalContact()->getLastName());
            $this->setNode($node, 'md:EmailAddress', $subscription->getTechnicalContact()->getEmail());
            $this->setNode($node, 'md:TelephoneNumber', $subscription->getTechnicalContact()->getPhone());
        }

        if ($subscription->getAdministrativeContact() instanceof Contact) {
            $node = $this->setNode($responseXml, 'md:ContactPerson', '', array('contactType' => 'administrative'));
            $this->setNode($node, 'md:GivenName', $subscription->getAdministrativeContact()->getFirstName());
            $this->setNode($node, 'md:SurName', $subscription->getAdministrativeContact()->getLastName());
            $this->setNode($node, 'md:EmailAddress', $subscription->getAdministrativeContact()->getEmail());
            $this->setNode($node, 'md:TelephoneNumber', $subscription->getAdministrativeContact()->getPhone());
        }

        // @todo: attributes

        $xml = $responseXml->asXML();

        $this->cache->save($cacheId, $xml);

        return $xml;
    }

    /**
     * @param \SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param string            $value
     * @param array             $attributes
     * @param array             $nss
     *
     * @return \SimpleXMLElement
     */
    private function setNode(\SimpleXMLElement $rootNode, $nodeName, $value, $attributes = array(), $nss = array())
    {
        $xpathExpression = './' . $nodeName;

        foreach ($attributes as $aName => $aValue) {
            $xpathExpression .= '[@' . $aName . '=\'' . $aValue . '\']';
        }

        foreach ($nss as $alias => $ns) {
            $rootNode->registerXPathNamespace($alias, $ns);
        }

        $node = $rootNode->xpath($xpathExpression);

        if (isset($node[0][0])) {
            $node[0][0] = $value;
            $node = $node[0];
        } else {
            $node = $rootNode->addChild($nodeName, $value);

            foreach ($attributes as $aName => $aValue) {
                $node->addAttribute($aName, $aValue);
            }
        }

        return $node;
    }
}
