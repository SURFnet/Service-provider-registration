<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add mail confirmation update templates.
 */
class Version20151113140551 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
<<<SQL
INSERT IGNORE INTO `Template` (`name`, `source`, `modified`) VALUES
(
  'confirmation.updated.en.html.twig',
  'Dear {{ subscription.contact.firstName}} {{ subscription.contact.lastName }},<br>

<p>Your subscription has been updated.</p>

<p>
    <b><a href="{{ url(\'form\', {id: subscription.id} ) }}">Link to form</a></b>
</p>

Kind regards,<br>
<br>
SURFconext',
NOW()
),(
  'confirmation.updated.nl.html.twig',
  'Beste {{ subscription.contact.firstName}} {{ subscription.contact.lastName }},<br>

<p>Je connectie is bijgewerkt.</p>

<p>
    <b><a href="{{ url(\'form\', {id: subscription.id} ) }}">Link naar formulier</a></b>
</p>

Met vriendelijke groet,<br>
<br>
SURFconext',
  NOW()
);
SQL
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
<<<SQL
DELETE IGNORE FROM Template
WHERE `name` IN
('confirmation.updated.en.html.twig', 'confirmation.updated.nl.html.twig');
SQL
        );
    }
}
