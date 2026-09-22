<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add an opaque uuid to the user table so the JWT can identify a user
 * without exposing the email (personal data) nor a guessable sequential id.
 */
final class Version20260922100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add opaque uuid column on user (used as JWT identity)';
    }

    public function up(Schema $schema): void
    {
        // 1. Add the column as nullable so existing rows can be backfilled.
        $this->addSql('ALTER TABLE `user` ADD uuid VARCHAR(36) DEFAULT NULL');

        // 2. Backfill existing users with random (v4) UUIDs.
        $userIds = $this->connection->fetchFirstColumn('SELECT id FROM `user`');
        foreach ($userIds as $id) {
            $this->addSql('UPDATE `user` SET uuid = :uuid WHERE id = :id', [
                'uuid' => self::generateUuidV4(),
                'id' => $id,
            ]);
        }

        // 3. Enforce the constraints once every row has a value.
        $this->addSql('ALTER TABLE `user` MODIFY uuid VARCHAR(36) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_UUID ON `user` (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_USER_UUID ON `user`');
        $this->addSql('ALTER TABLE `user` DROP uuid');
    }

    private static function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // version 4
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // variant RFC 4122

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
