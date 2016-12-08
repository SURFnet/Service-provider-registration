<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161208141826 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'ALTER TABLE Subscription '
            . 'ADD eduPersonScopedAffiliationAttribute '
            . 'LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', '
            . 'ADD eduPersonTargetedIDAttribute '
            . 'LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\''
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'ALTER TABLE Subscription DROP eduPersonScopedAffiliationAttribute, '
            . 'DROP eduPersonTargetedIDAttribute'
        );
    }
}
