<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181031122656 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A71F7E88B');
        $this->addSql('DROP INDEX IDX_5387574A71F7E88B ON events');
        $this->addSql('ALTER TABLE events CHANGE event_id mevent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A5FF045BF FOREIGN KEY (mevent_id) REFERENCES monitorizableEvent (id)');
        $this->addSql('CREATE INDEX IDX_5387574A5FF045BF ON events (mevent_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A5FF045BF');
        $this->addSql('DROP INDEX IDX_5387574A5FF045BF ON events');
        $this->addSql('ALTER TABLE events CHANGE mevent_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A71F7E88B FOREIGN KEY (event_id) REFERENCES monitorizableEvent (id)');
        $this->addSql('CREATE INDEX IDX_5387574A71F7E88B ON events (event_id)');
    }
}
