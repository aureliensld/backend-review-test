<?php

namespace App\Repository;

use App\Dto\EventInput;
use App\Entity\EventType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class DbalWriteEventRepository implements WriteEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function update(EventInput $authorInput, string $id): void
    {
        $sql = <<<SQL
        UPDATE event
        SET comment = :comment
        WHERE id = :id
SQL;

        $this->connection->executeQuery($sql, ['id' => $id, 'comment' => $authorInput->comment]);
    }

    public function bulkInsert(iterable $events, ?callable $onProgress = null): int
    {
        $sqlTemplate = <<<SQL
            WITH raw_data (
                    id, type, count, payload, create_at, comment,
                    actor_id, actor_login, actor_url, actor_avatar_url, 
                    repo_id, repo_name, repo_url
                ) AS (
                VALUES (
                    NULL::bigint, NULL::text, null::integer, null::jsonb, null::timestamp, null::text,
                    NULL::bigint, NULL::text, NULL::text, NULL::text, 
                    NULL::bigint, NULL::text, NULL::text
                ),
                %s
                OFFSET 1
            ),
            inserted_actors AS (
                INSERT INTO actor (id, login, url, avatar_url)
                    SELECT actor_id, actor_login, actor_url, actor_avatar_url
                    FROM raw_data
                    ORDER BY 1
                ON CONFLICT (id) DO NOTHING
            ),
            inserted_repos AS (
                INSERT INTO repo (id, name, url)
                    SELECT repo_id, repo_name, repo_url
                    FROM raw_data
                    ORDER BY 1
                ON CONFLICT (id) DO NOTHING
            )
            INSERT INTO "event" (id, actor_id, repo_id, type, count, payload, create_at, comment)
                SELECT id, actor_id, repo_id, type, count, payload, create_at, comment
                FROM raw_data
                ORDER BY 1
            ON CONFLICT (id) DO NOTHING
SQL;

        $connection = $this->connection;
        $counter = 0;
        $executeStatement = static function (array $params) use ($connection, &$sqlTemplate, $onProgress, &$counter): void {
            if (0 === \count($params)) {
                return;
            }

            $types = [
                Types::BIGINT,  // event.id
                'EventType',    // event.type
                Types::INTEGER, // event.count
                Types::STRING,  // event.payload (Manually encode json to keep memory consumption low)
                Types::STRING,  // event.create_at (the date is already formatted, send it as is to keep memory consumption low)
                Types::TEXT,    // event.comment
                Types::BIGINT,  // actor.id
                Types::STRING,  // actor.login
                Types::STRING,  // actor.url
                Types::STRING,  // actor.avatar_url
                Types::BIGINT,  // actor.id
                Types::STRING,  // actor.name
                Types::STRING,  // actor.url
            ];
            $boundedParameters = \count($types);
            $rowCount = (int) (\count($params) / $boundedParameters);

            $row = sprintf('(%s)', implode(', ', array_fill(0, $boundedParameters, '?')));
            $sql = sprintf($sqlTemplate, implode(', ', array_fill(0, $rowCount, $row)));

            $types = array_merge(...array_fill(0, $rowCount, $types)); // Duplicate as many as row inserted

            $connection->executeStatement($sql, $params, $types);

            if (null !== $onProgress) {
                $onProgress($counter);
            }
        };

        $params = [];
        foreach ($events as $event) {
            if (!isset(EventType::EVENT_TYPES[$event['type']])) {
                continue;
            }

            ++$counter;

            $params[] = $event['id'];
            $params[] = EventType::EVENT_TYPES[$event['type']];
            $params[] = $event['payload']['size'] ?? 1;
            $params[] = json_encode($event['payload'], \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);
            $params[] = $event['created_at'];
            $params[] = null;

            $params[] = $event['actor']['id'];
            $params[] = $event['actor']['login'];
            $params[] = $event['actor']['url'];
            $params[] = $event['actor']['avatar_url'];

            $params[] = $event['repo']['id'];
            $params[] = $event['repo']['name'];
            $params[] = $event['repo']['url'];

            $paramsCount = \count($params);
            if ($paramsCount > 65500) { // Limitation of the driver (number of parameters must be between 0 and 65535)
                $executeStatement($params);
                $params = [];
            }
        }

        $executeStatement($params);

        return $counter;
    }
}
