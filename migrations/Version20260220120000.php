<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour synchroniser le schéma avec les entités actuelles.
 * - Renomme client_id -> creator_id et agent_id -> assignee_id sur ticket
 * - Ajoute les colonnes name, role, created_at, updated_at sur user
 * - Ajoute description et updated_at sur category
 * - Ajuste updated_at sur comment (datetime_immutable)
 */
final class Version20260220120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronise le schéma de base de données avec les entités Doctrine actuelles';
    }

    public function up(Schema $schema): void
    {
        // User: ajouter name, role, created_at, updated_at
        $this->addSql('ALTER TABLE `user` ADD name VARCHAR(255) NOT NULL DEFAULT ""');
        $this->addSql('ALTER TABLE `user` ADD role VARCHAR(20) NOT NULL DEFAULT "ROLE_CLIENT"');
        $this->addSql('ALTER TABLE `user` ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE `user` ADD updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\'');

        // Category: ajouter description, updated_at, ajuster name
        $this->addSql('ALTER TABLE category ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD updated_at_new DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE category MODIFY name VARCHAR(255) NOT NULL');

        // Ticket: renommer client_id -> creator_id, agent_id -> assignee_id
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA319EB6921');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA33414710B');
        $this->addSql('ALTER TABLE ticket CHANGE client_id creator_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ticket CHANGE agent_id assignee_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ticket MODIFY updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA361220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA359EC7D60 FOREIGN KEY (assignee_id) REFERENCES `user` (id)');

        // Comment: ajuster updated_at
        $this->addSql('ALTER TABLE comment MODIFY updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // User: supprimer les colonnes ajoutées
        $this->addSql('ALTER TABLE `user` DROP COLUMN name');
        $this->addSql('ALTER TABLE `user` DROP COLUMN role');
        $this->addSql('ALTER TABLE `user` DROP COLUMN created_at');
        $this->addSql('ALTER TABLE `user` DROP COLUMN updated_at');

        // Category
        $this->addSql('ALTER TABLE category DROP COLUMN description');
        $this->addSql('ALTER TABLE category DROP COLUMN updated_at_new');
        $this->addSql('ALTER TABLE category MODIFY name VARCHAR(100) NOT NULL');

        // Ticket: restaurer les noms originaux
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA361220EA6');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA359EC7D60');
        $this->addSql('ALTER TABLE ticket CHANGE creator_id client_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ticket CHANGE assignee_id agent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA319EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA33414710B FOREIGN KEY (agent_id) REFERENCES `user` (id)');

        // Comment
        $this->addSql('ALTER TABLE comment MODIFY updated_at DATETIME NOT NULL');
    }
}
