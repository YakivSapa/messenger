<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20260611124118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ResetPasswordRequest table for password reset functionality';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ResetPasswordRequest (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashedToken VARCHAR(100) NOT NULL, requestedAt DATETIME NOT NULL, expiresAt DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_35370143A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE ResetPasswordRequest ADD CONSTRAINT FK_35370143A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ResetPasswordRequest DROP FOREIGN KEY FK_35370143A76ED395');
        $this->addSql('DROP TABLE ResetPasswordRequest');
    }
}
