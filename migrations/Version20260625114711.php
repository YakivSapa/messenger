<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625114711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create index for displayName in user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_user_displayName ON user (displayName)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_user_displayName ON User');
    }
}
