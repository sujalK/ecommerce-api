<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213213912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item CHANGE discount_amount unit_price_after_discount NUMERIC(10, 2) DEFAULT NULL');
        // $this->addSql('DROP INDEX UNIQ_IDENTIFIER_NAME ON shipping_method');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item CHANGE unit_price_after_discount discount_amount NUMERIC(10, 2) DEFAULT NULL');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_NAME ON shipping_method (name)');
    }
}
