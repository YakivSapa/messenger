<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260629112144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create friend_request table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE friend_request (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) DEFAULT \'pending\' NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, INDEX IDX_F284D94F624B39D (sender_id), INDEX IDX_F284D94CD53EDB6 (receiver_id), UNIQUE INDEX unique_pending_request (sender_id, receiver_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE friend_request ADD CONSTRAINT FK_F284D94F624B39D FOREIGN KEY (sender_id) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_request ADD CONSTRAINT FK_F284D94CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES User (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_F284D94F624B39D');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_F284D94CD53EDB6');
        $this->addSql('DROP TABLE friend_request');
    }
}
