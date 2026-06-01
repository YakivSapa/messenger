<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20260601144419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD uuid BINARY(16) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_UUID ON user (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_USER_UUID ON user');
        $this->addSql('ALTER TABLE user DROP uuid');
    }
}
