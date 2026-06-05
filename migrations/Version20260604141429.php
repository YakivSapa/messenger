<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260604141429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD username VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2DA17977F85E0677 ON user (username)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_2DA17977F85E0677 ON User');
        $this->addSql('ALTER TABLE User DROP username');
    }
}
