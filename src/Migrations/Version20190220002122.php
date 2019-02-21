<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190220002122 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE email ADD from_address VARCHAR(255) DEFAULT NULL, ADD to_address VARCHAR(255) DEFAULT NULL, DROP `from`, DROP `to`, CHANGE text text_content LONGTEXT DEFAULT NULL, CHANGE date created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE email ADD `from` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD `to` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP from_address, DROP to_address, CHANGE text_content text LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE created_at date DATETIME DEFAULT NULL');
    }
}
