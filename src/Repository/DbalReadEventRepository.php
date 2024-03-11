<?php

namespace App\Repository;

use App\Entity\EventType;
use App\Util\Pagination\CursorPaginator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

class DbalReadEventRepository implements ReadEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function countAll(\DateTimeImmutable $date, ?string $keyword = null): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('sum(count) as count')
            ->from('event')
            ->where('date(create_at) = :date')
            ->setParameter('date', $date, Types::DATE_IMMUTABLE)
        ;

        $this->addFTSWhereCondition($qb, $keyword);

        return $qb->fetchOne() ?? 0;
    }

    public function countByType(\DateTimeImmutable $date, ?string $keyword = null): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('type, sum(count) as count')
            ->from('event')
            ->where('date(create_at) = :date')
            ->setParameter('date', $date, Types::DATE_IMMUTABLE)
            ->groupBy('type')
        ;

        $this->addFTSWhereCondition($qb, $keyword);

        return $qb->fetchAllKeyValue();
    }

    public function statsByTypePerHour(\DateTimeImmutable $date, ?string $keyword = null): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('extract(hour from create_at) as hour, type, sum(count) as count')
            ->from('event')
            ->where('date(create_at) = :date')
            ->setParameter('date', $date, Types::DATE_IMMUTABLE)
            ->groupBy(['type', 'EXTRACT(hour from create_at)'])
        ;

        $this->addFTSWhereCondition($qb, $keyword);

        $results = $qb->fetchAllAssociative();
        $data = array_fill(0, 24, array_fill_keys(EventType::getValues(), 0));

        foreach ($results as $stat) {
            $data[(int) $stat['hour']][$stat['type']] = $stat['count'];
        }

        return $data;
    }

    public function getLatest(\DateTimeImmutable $date, ?string $keyword = null, int $offset = 0, ?int $maxResults = null): iterable
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
                'event.id', 'event.type', 'event.count', 'event.payload', 'event.create_at', 'event.comment',
                'repo.id as repo_id', 'repo.name as repo_name', 'repo.url as repo_url',
                'actor.id as actor_id', 'actor.login as actor_login', 'actor.url as actor_url', 'actor.avatar_url as actor_avatar_url',
            )
            ->from('event', 'event')
            ->innerJoin('event', 'repo', 'repo', 'repo.id = event.repo_id')
            ->innerJoin('event', 'actor', 'actor', 'actor.id = event.actor_id')
            ->where('date(create_at) = :date')
            ->setParameter('date', $date, Types::DATE_IMMUTABLE)
            ->setFirstResult($offset)
            ->orderBy('event.id')
        ;

        $this->addFTSWhereCondition($qb, $keyword);
        if (null !== $maxResults) {
            $qb->setMaxResults($maxResults + 1);
        }

        $results = array_map(static function ($item) {
            $item['repo'] = [
                'id' => $item['repo_id'],
                'name' => $item['repo_name'],
                'url' => $item['repo_url'],
            ];
            unset($item['repo_id'], $item['repo_name'], $item['repo_url']);

            $item['actor'] = [
                'id' => $item['actor_id'],
                'login' => $item['actor_login'],
                'url' => $item['actor_avatar_url'],
                'avatar_url' => $item['actor_avatar_url'],
            ];
            unset($item['actor_id'], $item['actor_login'], $item['actor_url'], $item['actor_avatar_url']);

            return $item;
        }, $qb->fetchAllAssociative());

        if (null === $maxResults) {
            return $results;
        }

        if (\count($results) < $maxResults + 1) {
            return $results;
        }

        array_pop($results);

        return new CursorPaginator($results, $offset + $maxResults);
    }

    public function exist(string $id): bool
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('1')
            ->from('event')
            ->where('id = :id')
            ->setParameter('id', $id, Types::BIGINT)
        ;

        return (bool) $qb->fetchOne();
    }

    private function addFTSWhereCondition(QueryBuilder $qb, ?string $keyword): void
    {
        if (null === $keyword) {
            return;
        }

        $FTSParamName = uniqid();
        $FTSConfigParamName = uniqid('fts_config_');

        $qb
            ->andWhere(sprintf('search_ts @@ to_tsquery(:%s, :%s)', $FTSConfigParamName, $FTSParamName))
            ->setParameter($FTSConfigParamName, 'english')
            ->setParameter($FTSParamName, $keyword)
        ;
    }
}
