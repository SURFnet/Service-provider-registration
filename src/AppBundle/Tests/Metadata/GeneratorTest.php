<?php

namespace AppBundle\Tests\Metadata;

use AppBundle\Entity\Subscription;
use AppBundle\Metadata\Fetcher;
use AppBundle\Metadata\Generator;
use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use Doctrine\Common\Cache\ArrayCache;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Response
     */
    private $mockResponse;

    /**
     * @var Generator
     */
    private $generator;

    public function setup()
    {
        $this->mockResponse = new Response(200);
        $this->mockResponse->setBody(fopen(__DIR__ . '/Fixtures/metadata_lean.xml', 'r+'));

        $plugin = new MockPlugin();
        $plugin->addResponse($this->mockResponse);

        $guzzle = new Client();
        $guzzle->addSubscriber($plugin);

        $fetcher = new Fetcher(
            $guzzle,
            new ArrayCache(),
            new Logger('test', array(new NullHandler()))
        );

        $this->generator = new Generator(
            $fetcher,
            new ArrayCache(),
            new Logger('test', array(new NullHandler()))
        );
    }

    public function testSuccess()
    {
        $subscription = new Subscription();
        $subscription->setNameNl('UNAMENL');
        $subscription->setNameEn('UNAMEEN');
        $subscription->setDescriptionNl('UPDATEDDESCRNL');
        $subscription->setDescriptionEn('UPDATEDDESCREN');
        $subscription->setApplicationUrl('http://www.google.nl');
        $subscription->setLogoUrl('http://www.google.com');

        $contact = new Contact();
        $contact->setFirstName('Henk');
        $contact->setLastName('Henksma');
        $contact->setEmail('henk@henkie.org');
        $contact->setPhone('+51632132145');
        $subscription->setSupportContact($contact);

        $contact = new Contact();
        $contact->setFirstName('Henk2');
        $contact->setLastName('Henksma2');
        $contact->setEmail('henk2@henkie.org');
        $contact->setPhone('+51639992145');
        $subscription->setAdministrativeContact($contact);

        $attr = new Attribute();
        $attr->setRequested(true);

        $subscription->setGivenNameAttribute($attr);
        $subscription->setUidAttribute($attr);
        $subscription->setEntitlementAttribute($attr);

        $attr = new Attribute();
        $attr->setRequested(false);

        $subscription->setCommonNameAttribute($attr);

        $xml = $this->generator->generate($subscription);

        $this->assertContains('<mdui:DisplayName xml:lang="nl">UNAMENL</mdui:DisplayName>', $xml);
        $this->assertContains('<mdui:DisplayName xml:lang="en">UNAMEEN</mdui:DisplayName>', $xml);
        $this->assertContains('<mdui:Description xml:lang="nl">UPDATEDDESCRNL</mdui:Description>', $xml);
        $this->assertContains('<mdui:Description xml:lang="en">UPDATEDDESCREN</mdui:Description>', $xml);
        $this->assertContains('<mdui:InformationURL xml:lang="en">http://www.google.nl</mdui:InformationURL>', $xml);
        $this->assertContains('<mdui:InformationURL xml:lang="nl">URLNL</mdui:InformationURL>', $xml);
        $this->assertContains('<mdui:Logo>http://www.google.com</mdui:Logo>', $xml);

        // Created
        $this->assertContains('<md:ContactPerson contactType="support">', $xml);
        $this->assertContains('<md:GivenName>Henk</md:GivenName>', $xml);
        $this->assertContains('<md:SurName>Henksma</md:SurName>', $xml);
        $this->assertContains('<md:EmailAddress>henk@henkie.org</md:EmailAddress>', $xml);
        $this->assertContains('<md:TelephoneNumber>+51632132145</md:TelephoneNumber>', $xml);

        // Replaced
        $this->assertContains('<md:ContactPerson contactType="administrative">', $xml);
        $this->assertContains('<md:GivenName>Henk2</md:GivenName>', $xml);
        $this->assertContains('<md:SurName>Henksma2</md:SurName>', $xml);
        $this->assertContains('<md:EmailAddress>henk2@henkie.org</md:EmailAddress>', $xml);
        $this->assertContains('<md:TelephoneNumber>+51639992145</md:TelephoneNumber>', $xml);

        // Untouched
        $this->assertContains('<md:ContactPerson contactType="technical">', $xml);
        $this->assertContains('<md:GivenName>Test</md:GivenName>', $xml);
        $this->assertContains('<md:SurName>Tester</md:SurName>', $xml);
        $this->assertContains('<md:EmailAddress>test@domain.org</md:EmailAddress>', $xml);
        $this->assertContains('<md:TelephoneNumber>123456789</md:TelephoneNumber>', $xml);

        // Created non existing attribute based on first key
        $this->assertContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:eduPersonEntitlement"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"', $xml);

        // Replaced existing attributes based on first key
        $this->assertContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:givenName"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:2.5.4.42"', $xml);

        // Replaced existing attributes based on second key
        $this->assertContains('md:RequestedAttribute Name="urn:oid:0.9.2342.19200300.100.1.1"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:uid"', $xml);

        // Non used attribute should not appear
        $this->assertNotContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:eduPersonOrgUnitDN"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.4"', $xml);

        // Non requested attribute should not appear
        $this->assertNotContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:cn"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:2.5.4.3"', $xml);

        // Removed existing attribute based on first key
        $this->assertNotContains(
            'md:RequestedAttribute Name="urn:mace:dir:attribute-def:schacPersonalUniqueCode"',
            $xml
        );
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:1.3.6.1.4.1.1466.155.121.1.15"', $xml);

        // Removed existing attribute based on second key
        $this->assertNotContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:preferredLanguage"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:2.16.840.1.113730.3.1.39"', $xml);
    }
}
