<?php

namespace AppBundle\Metadata;

class AttributesMetadataRepository
{
    private $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function findAll()
    {
        return json_decode(
            file_get_contents($this->rootDir . '/Resources/attributes.json'),
        );
    }
}
