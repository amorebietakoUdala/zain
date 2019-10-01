<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181031125825 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A5FF045BF');
        $this->addSql('DROP INDEX IDX_5387574A5FF045BF ON events');
        $this->addSql('ALTER TABLE events CHANGE mevent_id monitorizable_event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A547A83C7 FOREIGN KEY (monitorizable_event_id) REFERENCES monitorizableEvent (id)');
        $this->addSql('CREATE INDEX IDX_5387574A547A83C7 ON events (monitorizable_event_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A547A83C7');
        $this->addSql('DROP INDEX IDX_5387574A547A83C7 ON events');
        $this->addSql('ALTER TABLE events CHANGE monitorizable_event_id mevent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A5FF045BF FOREIGN KEY (mevent_id) REFERENCES monitorizableEvent (id)');
        $this->addSql('CREATE INDEX IDX_5387574A5FF045BF ON events (mevent_id)');
    }
}
