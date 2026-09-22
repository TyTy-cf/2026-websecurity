<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260922121827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a random, non-personal uuid identifier used in the JWT payload instead of the email.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD uuid VARCHAR(36) DEFAULT NULL');
        $this->addSql('UPDATE user SET uuid = UUID() WHERE uuid IS NULL');
        $this->addSql('ALTER TABLE user MODIFY uuid VARCHAR(36) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_UUID ON user (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_UUID ON `user`');
        $this->addSql('ALTER TABLE `user` DROP uuid');
    }
}
