<?php

namespace AppBundle\Tests\Metadata;

use AppBundle\Entity\Subscription;
use AppBundle\Metadata\Fetcher;
use AppBundle\Metadata\Generator;
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
        $this->mockResponse->setBody(fopen(__DIR__ . '/Fixtures/metadata.xml', 'r+'));

        $plugin = new MockPlugin();
        $plugin->addResponse($this->mockResponse);

        $guzzle = new Client();
        $guzzle->addSubscriber($plugin);

        $fetcher = new Fetcher(
            $guzzle,
            new ArrayCache(),
            new Logger('test', array(new NullHandler()))
        );

        $this->generator = new Generator($fetcher);
    }

    public function testSuccess()
    {
        $subscription = new Subscription();
        $subscription->setLogoUrl('http://www.google.com');
        $subscription->setDescriptionEn('HENKIE!!!');

        $contact = new Contact();
        $contact->setFirstName('Henk');
        $contact->setLastName('Henksma');
        $contact->setEmail('henk@henkie.org');
        $contact->setPhone('+51632132145');

        $subscription->setSupportContact($contact);
        $subscription->setTechnicalContact($contact);
        $subscription->setAdministrativeContact($contact);

        $xml = $this->generator->generate($subscription);

        var_dump($xml);
    }
}
