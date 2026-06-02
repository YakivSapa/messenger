<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602133851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('DROP INDEX uniq_user_uuid ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2DA17977D17F50A6 ON user (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE User CHANGE id id INT NOT NULL');
        $this->addSql('DROP INDEX uniq_2da17977d17f50a6 ON User');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_UUID ON User (uuid)');
    }
}
