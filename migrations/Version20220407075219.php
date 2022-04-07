<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220407075219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the configuration table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE configuration (id INT AUTO_INCREMENT NOT NULL, blog_title VARCHAR(255) NOT NULL, blog_description VARCHAR(255) NOT NULL, copyright_message VARCHAR(255) NOT NULL, copyright_extra_message VARCHAR(255) DEFAULT NULL, linkedin_username VARCHAR(255) DEFAULT NULL, github_username VARCHAR(255) DEFAULT NULL, google_analytics_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE configuration');
    }
}
