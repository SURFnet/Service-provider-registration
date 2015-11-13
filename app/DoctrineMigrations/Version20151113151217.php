<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrate all subscriptions from old Finished status to new Finished status.
 */
class Version20151113151217 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE Subscription SET status=2 WHERE status=1;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('UPDATE Subscription SET status=1 WHERE status=2;');
    }
}
