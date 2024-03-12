<?php

namespace App\Repository;

interface ReadEventRepository
{
    public function countAll(\DateTimeImmutable $date, ?string $keyword = null): int;

    /**
     * @return array<string, int>
     */
    public function countByType(\DateTimeImmutable $date, ?string $keyword = null): array;

    /**
     * @return array<string, int>[]
     */
    public function statsByTypePerHour(\DateTimeImmutable $date, ?string $keyword = null): array;

    /**
     * @return array{
     *      id: int|string,
     *      type: string,
     *      count: int,
     *      payload: array<string, mixed>,
     *      created_at: string,
     *      comment: string,
     *      actor: array{id: int|string, login: string, url: string, avatar_url: string},
     *      repo: array{id: int|string, name: string, url: string}
     *  }[]
     */
    public function getLatest(\DateTimeImmutable $date, ?string $keyword = null, int $offset = 0, ?int $maxResults = null): iterable;

    public function exist(string $id): bool;
}
