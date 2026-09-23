<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922122100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user.crypted_id (opaque JWT identity)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD crypted_id VARCHAR(32) DEFAULT NULL');
        $this->addSql("UPDATE `user` SET crypted_id = LEFT(SHA2(CONCAT(id, RAND(), UUID(), NOW(6)), 256), 32) WHERE crypted_id IS NULL");
        $this->addSql('ALTER TABLE `user` CHANGE crypted_id crypted_id VARCHAR(32) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_CRYPTED_ID ON `user` (crypted_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_CRYPTED_ID ON `user`');
        $this->addSql('ALTER TABLE `user` DROP crypted_id');
    }
}
