<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220421113458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE posts ADD obsolete TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX online ON posts (online)');
        $this->addSql('CREATE INDEX summary ON posts (summary)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX online ON posts');
        $this->addSql('DROP INDEX summary ON posts');
        $this->addSql('ALTER TABLE posts DROP obsolete');
    }
}
