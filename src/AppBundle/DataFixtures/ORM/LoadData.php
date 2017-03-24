<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Template;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadData
 */
class LoadData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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

        $template = new Template();
        $template->setName('confirmation.published.nl.html.twig');
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
        $template->setName('confirmation.published.en.html.twig');
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

        $template = new Template();
        $template->setName('confirmation.finished.nl.html.twig');
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
        $template->setName('confirmation.finished.en.html.twig');
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

        $template = new Template();
        $template->setName('confirmation.updated.en.html.twig');
        $template->setSource(
            <<<'EOT'
Dear {{ subscription.contact.firstName}} {{ subscription.contact.lastName }},<br>

<p>Your subscription has been updated.</p>

<p>
    <b><a href="{{ url('form', {id: subscription.id} ) }}">Link to form</a></b>
</p>

Kind regards,<br>
<br>
SURFconext
EOT
        );
        $manager->persist($template);

        $template = new Template();
        $template->setName('confirmation.updated.nl.html.twig');
        $template->setSource(
            <<<'EOT'
Beste {{ subscription.contact.firstName}} {{ subscription.contact.lastName }},<br>

<p>Je connectie is bijgewerkt.</p>

<p>
    <b><a href="{{ url('form', {id: subscription.id} ) }}">Link naar formulier</a></b>
</p>

Met vriendelijke groet,<br>
<br>
SURFconext
EOT
        );
        $manager->persist($template);

        $manager->flush();
    }
}
