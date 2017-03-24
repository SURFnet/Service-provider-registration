<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170322151551 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lexik_trans_unit_translations (id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, trans_unit_id INT DEFAULT NULL, locale VARCHAR(10) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B0AA394493CB796C (file_id), INDEX IDX_B0AA3944C3C583C9 (trans_unit_id), UNIQUE INDEX trans_unit_locale_idx (trans_unit_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lexik_translation_file (id INT AUTO_INCREMENT NOT NULL, domain VARCHAR(255) NOT NULL, locale VARCHAR(10) NOT NULL, extention VARCHAR(10) NOT NULL, path VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, UNIQUE INDEX hash_idx (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lexik_trans_unit (id INT AUTO_INCREMENT NOT NULL, key_name VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX key_domain_idx (key_name, domain), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Subscription (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', locale VARCHAR(255) NOT NULL, archived TINYINT(1) NOT NULL, environment VARCHAR(255) NOT NULL, status INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, ticketNo VARCHAR(255) NOT NULL, janusId VARCHAR(255) DEFAULT NULL, contact LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', importUrl VARCHAR(255) DEFAULT NULL, metadataUrl VARCHAR(255) DEFAULT NULL, metadataXml LONGTEXT DEFAULT NULL, acsLocation VARCHAR(255) DEFAULT NULL, entityId VARCHAR(255) DEFAULT NULL, certificate LONGTEXT DEFAULT NULL, logoUrl VARCHAR(255) DEFAULT NULL, nameNl VARCHAR(255) DEFAULT NULL, nameEn VARCHAR(255) DEFAULT NULL, descriptionNl LONGTEXT DEFAULT NULL, descriptionEn LONGTEXT DEFAULT NULL, applicationUrl VARCHAR(255) DEFAULT NULL, eulaUrl VARCHAR(255) DEFAULT NULL, administrativeContact LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', technicalContact LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', supportContact LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', givenNameAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', surNameAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', commonNameAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', displayNameAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', emailAddressAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', organizationAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', organizationTypeAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', affiliationAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', entitlementAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', principleNameAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', uidAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', preferredLanguageAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', personalCodeAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', scopedAffiliationAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', eduPersonTargetedIDAttribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', comments LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Template (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, source LONGTEXT NOT NULL, modified DATETIME NOT NULL, UNIQUE INDEX UNIQ_6E167DD55E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SubscriptionStatusChange (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', subscriptionId CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fromStatus INT DEFAULT NULL, toStatus INT NOT NULL, createdAt DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lexik_trans_unit_translations ADD CONSTRAINT FK_B0AA394493CB796C FOREIGN KEY (file_id) REFERENCES lexik_translation_file (id)');
        $this->addSql('ALTER TABLE lexik_trans_unit_translations ADD CONSTRAINT FK_B0AA3944C3C583C9 FOREIGN KEY (trans_unit_id) REFERENCES lexik_trans_unit (id)');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lexik_trans_unit_translations DROP FOREIGN KEY FK_B0AA394493CB796C');
        $this->addSql('ALTER TABLE lexik_trans_unit_translations DROP FOREIGN KEY FK_B0AA3944C3C583C9');
        $this->addSql('DROP TABLE lexik_trans_unit_translations');
        $this->addSql('DROP TABLE lexik_translation_file');
        $this->addSql('DROP TABLE lexik_trans_unit');
        $this->addSql('DROP TABLE Subscription');
        $this->addSql('DROP TABLE Template');
        $this->addSql('DROP TABLE SubscriptionStatusChange');
    }
}
