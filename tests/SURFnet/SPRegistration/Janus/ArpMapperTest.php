<?php

namespace SURFnet\SPRegistration\Janus;

use AppBundle\Entity\Subscription;
use PHPUnit_Framework_TestCase;

class ArpMapperTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyArpMapping()
    {
        $request = new Subscription();

        $mapper = new ArpMapper();
        $arp = $mapper->mapRequestToArpAttributes($request);

        $this->assertNotEmpty($arp);
        $this->assertInstanceOf(
            'OpenConext\JanusClient\ArpAttributes',
            $arp
        );
        $this->assertEmpty($arp->toArray());
    }
}
