<?php

namespace SURFnet\SPRegistration\Janus;

use AppBundle\Entity\Subscription;
use AppBundle\Metadata\AttributesMetadataRepository;
use AppBundle\Model\Attribute;
use OpenConext\JanusClient\ArpAttributes;

/**
 * Class ArpMapper
 * @package SURFnet\SPRegistration\Janus
 */
final class ArpMapper
{
    /**
     * @var AttributesMetadataRepository
     */
    private $attributesMetadataRepository;

    /**
     * ArpMapper constructor.
     *
     * @param AttributesMetadataRepository $attributesMetadataRepository
     */
    public function __construct($attributesMetadataRepository)
    {
        $this->attributesMetadataRepository = $attributesMetadataRepository;
    }

    /**
     * @param Subscription $request
     * @return ArpAttributes
     */
    public function mapRequestToArpAttributes(Subscription $request)
    {
        $arp = array();
        $attributesMetadata = $this->attributesMetadataRepository->findAll();
        foreach ($attributesMetadata as $attributeMetadata) {
            $getter = 'get' . ucfirst($attributeMetadata->id) . 'Attribute';
            $attr = $request->$getter();

            if (!$attr instanceof Attribute) {
                continue;
            }

            if (!$attr->isRequested()) {
                continue;
            }

            $attributeMaceId = $attributeMetadata->urns[0];
            $arp[$attributeMaceId] = array('*');
        }

        return new ArpAttributes($arp);
    }
}
