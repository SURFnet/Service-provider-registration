<?php

namespace AppBundle\Metadata;

use stdClass;

/**
 * Class AttributesMetadataRepository
 */
class AttributesMetadataRepository
{
    private $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @return stdClass
     */
    public function findAll()
    {
        return json_decode(
            file_get_contents($this->rootDir . '/Resources/attributes.json')
        );
    }
}
