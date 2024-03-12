<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240311182217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ALTER actor_id SET NOT NULL');
        $this->addSql('ALTER TABLE event ALTER repo_id SET NOT NULL');

        $this->addSql('ALTER TABLE event ADD COLUMN search_ts tsvector GENERATED ALWAYS AS (to_tsvector(\'english\', COALESCE(jsonb_path_query_array(payload->\'commits\', \'$[*]."message"\'), payload->\'pull_request\'->\'body\', payload->\'comment\'->\'body\'))) STORED');
        $this->addSql('CREATE INDEX event_search_ts_idx ON event USING GIN (search_ts)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ALTER actor_id DROP NOT NULL');
        $this->addSql('ALTER TABLE event ALTER repo_id DROP NOT NULL');

        $this->addSql('DROP INDEX event_search_ts_idx');
        $this->addSql('ALTER TABLE event DROP COLUMN search_ts');
    }
}
