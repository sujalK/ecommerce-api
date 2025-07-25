<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213202026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart ADD currency VARCHAR(10) DEFAULT NULL');
        // $this->addSql('DROP INDEX UNIQ_IDENTIFIER_NAME ON shipping_method');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart DROP currency');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_NAME ON shipping_method (name)');
    }
}
