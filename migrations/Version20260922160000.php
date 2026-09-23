<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Exercice 8 : ajoute un identifiant public `uuid` à la table user (utilisé dans le JWT).
 */
final class Version20260922160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne uuid (unique) sur user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD uuid VARCHAR(36) DEFAULT NULL');
        $this->addSql('UPDATE `user` SET uuid = UUID() WHERE uuid IS NULL');
        $this->addSql('ALTER TABLE `user` MODIFY uuid VARCHAR(36) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_UUID ON `user` (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_USER_UUID ON `user`');
        $this->addSql('ALTER TABLE `user` DROP uuid');
    }
}
