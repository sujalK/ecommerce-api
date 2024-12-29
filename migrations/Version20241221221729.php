<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221221729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment CHANGE order_id order_id INT DEFAULT NULL, CHANGE payment_method payment_method VARCHAR(50) DEFAULT NULL, CHANGE amount amount NUMERIC(10, 2) DEFAULT NULL, CHANGE payment_date payment_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE transaction_id transaction_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment CHANGE order_id order_id INT NOT NULL, CHANGE payment_method payment_method VARCHAR(50) NOT NULL, CHANGE amount amount NUMERIC(10, 2) NOT NULL, CHANGE payment_date payment_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE transaction_id transaction_id VARCHAR(255) NOT NULL');
    }
}
