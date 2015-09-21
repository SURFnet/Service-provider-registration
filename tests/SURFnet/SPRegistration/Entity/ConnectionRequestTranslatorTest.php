<?php

namespace SURFnet\SPRegistration\Entity;

use AppBundle\Entity\Subscription;
use PHPUnit_Framework_TestCase;
use SURFnet\SPRegistration\Janus\ArpMapper;
use SURFnet\SPRegistration\Janus\ContactMapper;
use SURFnet\SPRegistration\Janus\MetadataMapper;

class ConnectionRequestTranslatorTest extends PHPUnit_Framework_TestCase
{
    public function testTranslate() {
        $translator = new ConnectionRequestTranslator(
            new ArpMapper(),
            new ContactMapper(),
            new MetadataMapper()
        );
        var_dump($translator->translateToConnection(new Subscription()));
    }
}
