<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20260608104424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make displayName non-nullable for user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE displayName displayName VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE displayName displayName VARCHAR(255) DEFAULT NULL');
    }
}
