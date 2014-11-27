<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Template;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $template = new Template();
        $template->setName('invitation.nl.html.twig');
        $template->setSource(
            <<<'EOT'
            Beste {{ subscription.contact.firstName}} {{ subscription.contact.lastName }},<br>

<p>Bla bla</p>

<p>
    <b><a href="{{ url('form', {id: subscription.id} ) }}">Link naar formulier</a></b>
</p>

Met vriendelijke groet,<br>
<br>
SURFconext
EOT
        );
        $manager->persist($template);

        $template = new Template();
        $template->setName('invitation.en.html.twig');
        $template->setSource(
            <<<'EOT'
            Dear {{ subscription.contact.firstName}} {{ subscription.contact.lastName }},<br>

<p>Bla bla</p>

<p>
    <b><a href="{{ url('form', {id: subscription.id} ) }}">Link to form</a></b>
</p>

Kind regards,<br>
<br>
SURFconext
EOT
        );
        $manager->persist($template);

        $manager->flush();
    }
}
