<?php

namespace App\Repository;

use App\Dto\EventInput;

interface WriteEventRepository
{
    public function update(EventInput $authorInput, string $id): void;

    /**
     * @param iterable<array{
     *     id: int|string,
     *     type: string,
     *     payload: array{size?: int, ...},
     *     created_at: string,
     *     actor: array{id: int|string, login: string, url: string, avatar_url: string},
     *     repo: array{id: int|string, name: string, url: string}
     * }> $events
     * @param callable(int $processedEvents): void|null $onProgress Callback to track inserting. $processedEvents is the number of event inserted so far
     *
     * @return int The number of stored events
     */
    public function bulkInsert(iterable $events, ?callable $onProgress = null): int;
}
