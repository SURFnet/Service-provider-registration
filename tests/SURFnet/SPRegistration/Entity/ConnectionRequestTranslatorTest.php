<?php

namespace SURFnet\SPRegistration\Entity;

use AppBundle\Entity\Subscription;
use OpenConext\JanusClient\Entity\Connection;
use PHPUnit_Framework_TestCase;
use SURFnet\SPRegistration\Janus\ArpMapper;
use SURFnet\SPRegistration\Janus\ContactMapper;
use SURFnet\SPRegistration\Janus\MetadataMapper;

/**
 * Class ConnectionRequestTranslatorTest
 * @package SURFnet\SPRegistration\Entity
 */
class ConnectionRequestTranslatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Testing what happpens when you translate an empty Subscription.
     */
    public function testTranslateEmptySubscription()
    {
        $translator = new ConnectionRequestTranslator(
            new ArpMapper(),
            new ContactMapper(),
            new MetadataMapper()
        );
        $translator->translateToConnection(new Subscription());
    }

    /**
     *
     */
    public function testTranslateMinimalConnection()
    {
        $translator = new ConnectionRequestTranslator(
            new ArpMapper(),
            new ContactMapper(),
            new MetadataMapper()
        );
        $translator->translateFromConnection(
            new Connection(
                'http://mock-sp',
                Connection::TYPE_SP,
                Connection::WORKFLOW_TEST,
                array(),
                ''
            ),
            new Subscription()
        );
    }
}
