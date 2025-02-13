<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212124320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE status status VARCHAR(20) DEFAULT NULL, CHANGE payment_status payment_status VARCHAR(20) DEFAULT NULL, CHANGE currency currency VARCHAR(10) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_SHIPPING_METHOD_NAME ON shipping_method (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE status status VARCHAR(20) NOT NULL, CHANGE payment_status payment_status VARCHAR(20) NOT NULL, CHANGE currency currency VARCHAR(10) NOT NULL');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_SHIPPING_METHOD_NAME ON shipping_method');
    }
}
