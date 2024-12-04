<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024131659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des tables user et task pour la base de données de test';
    }

    public function up(Schema $schema): void
    {
       // Création de la table `user`
       $this->addSql('CREATE TABLE user (
        id INT AUTO_INCREMENT NOT NULL,
        username VARCHAR(25) NOT NULL,
        password VARCHAR(64) NOT NULL,
        email VARCHAR(180) NOT NULL,
        roles JSON NOT NULL,
        reset_token VARCHAR(255) DEFAULT NULL,
        token_expiry_date DATETIME DEFAULT NULL,
        PRIMARY KEY(id),
        UNIQUE(email),
        UNIQUE(username)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

    // Création de la table `task`
    $this->addSql('CREATE TABLE task (
        id INT AUTO_INCREMENT NOT NULL,
        author_id INT NOT NULL,
        create_at DATETIME NOT NULL,
        title VARCHAR(255) NOT NULL,
        content LONGTEXT NOT NULL,
        is_done TINYINT(1) NOT NULL,
        PRIMARY KEY(id),
        INDEX IDX_527EDB25F675F31B (author_id),
        CONSTRAINT FK_527EDB25F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
}


    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25F675F31B');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE user');
}
}