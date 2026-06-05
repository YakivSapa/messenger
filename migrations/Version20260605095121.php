<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20260605095121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make username non-nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(32) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE User CHANGE username username VARCHAR(32) DEFAULT NULL');
    }
}
