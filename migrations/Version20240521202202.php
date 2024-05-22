<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240521202202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE api_token DROP CONSTRAINT fk_7ba2f5ebd56ae7ed');
        $this->addSql('DROP INDEX idx_7ba2f5ebd56ae7ed');
        $this->addSql('ALTER TABLE api_token RENAME COLUMN onwned_by_id TO owned_by_id');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EB5E70BCD7 FOREIGN KEY (owned_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7BA2F5EB5E70BCD7 ON api_token (owned_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE api_token DROP CONSTRAINT FK_7BA2F5EB5E70BCD7');
        $this->addSql('DROP INDEX IDX_7BA2F5EB5E70BCD7');
        $this->addSql('ALTER TABLE api_token RENAME COLUMN owned_by_id TO onwned_by_id');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT fk_7ba2f5ebd56ae7ed FOREIGN KEY (onwned_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_7ba2f5ebd56ae7ed ON api_token (onwned_by_id)');
    }
}
