<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190826075218 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE compt_bancaire (id INT AUTO_INCREMENT NOT NULL, partenaire_id INT NOT NULL, num_compt VARCHAR(255) NOT NULL, solde BIGINT NOT NULL, INDEX IDX_A53C254498DE13AC (partenaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commission (id INT AUTO_INCREMENT NOT NULL, etat INT NOT NULL, partenaire INT NOT NULL, envoi INT NOT NULL, retrait INT NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarifs (id INT AUTO_INCREMENT NOT NULL, borne_inferieur INT NOT NULL, borne_superieur INT NOT NULL, valeur INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partenaires (id INT AUTO_INCREMENT NOT NULL, raison_social VARCHAR(255) NOT NULL, ninea VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D230102EC678AEBE (ninea), UNIQUE INDEX UNIQ_D230102E450FF010 (telephone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom_e VARCHAR(255) DEFAULT NULL, prenom_e VARCHAR(255) DEFAULT NULL, cniE VARCHAR(255) NOT NULL, nom_b VARCHAR(255) DEFAULT NULL, prenom_b VARCHAR(255) DEFAULT NULL, cniB VARCHAR(255) NOT NULL, date_trans DATETIME NOT NULL, envoi VARCHAR(255) DEFAULT NULL, retrait VARCHAR(255) DEFAULT NULL, telephoneE VARCHAR(255) NOT NULL, telephoneB VARCHAR(255) DEFAULT NULL, montant INT DEFAULT NULL, montantpaye INT DEFAULT NULL, code_trans VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_723705D1F817042A (cniE), UNIQUE INDEX UNIQ_723705D166739189 (cniB), UNIQUE INDEX UNIQ_723705D1C9464506 (telephoneE), UNIQUE INDEX UNIQ_723705D15722D0A5 (telephoneB), INDEX IDX_723705D1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depot (id INT AUTO_INCREMENT NOT NULL, cassier_id INT DEFAULT NULL, numero_compt_id INT NOT NULL, montant BIGINT NOT NULL, date_depot DATETIME NOT NULL, INDEX IDX_47948BBC4B02CCBD (cassier_id), INDEX IDX_47948BBCD74A54FF (numero_compt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, partenaire_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, image_name VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL, compte_bancaire BIGINT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649450FF010 (telephone), INDEX IDX_8D93D64998DE13AC (partenaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE retrait (id INT AUTO_INCREMENT NOT NULL, transaction_id INT DEFAULT NULL, user_id INT DEFAULT NULL, code VARCHAR(255) DEFAULT NULL, dateretrait DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_D9846A512FC0CB0F (transaction_id), INDEX IDX_D9846A51A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compt_bancaire ADD CONSTRAINT FK_A53C254498DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaires (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE depot ADD CONSTRAINT FK_47948BBC4B02CCBD FOREIGN KEY (cassier_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE depot ADD CONSTRAINT FK_47948BBCD74A54FF FOREIGN KEY (numero_compt_id) REFERENCES compt_bancaire (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64998DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaires (id)');
        $this->addSql('ALTER TABLE retrait ADD CONSTRAINT FK_D9846A512FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE retrait ADD CONSTRAINT FK_D9846A51A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE depot DROP FOREIGN KEY FK_47948BBCD74A54FF');
        $this->addSql('ALTER TABLE compt_bancaire DROP FOREIGN KEY FK_A53C254498DE13AC');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64998DE13AC');
        $this->addSql('ALTER TABLE retrait DROP FOREIGN KEY FK_D9846A512FC0CB0F');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE depot DROP FOREIGN KEY FK_47948BBC4B02CCBD');
        $this->addSql('ALTER TABLE retrait DROP FOREIGN KEY FK_D9846A51A76ED395');
        $this->addSql('DROP TABLE compt_bancaire');
        $this->addSql('DROP TABLE commission');
        $this->addSql('DROP TABLE tarifs');
        $this->addSql('DROP TABLE partenaires');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE depot');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE retrait');
    }
}
