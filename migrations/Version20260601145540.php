<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601145540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('DROP INDEX uniq_user_uuid ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_UUID ON user (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE uuid uuid BINARY(16) DEFAULT NULL');
        $this->addSql('DROP INDEX uniq_user_uuid ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_UUID ON user (uuid)');
    }
}
