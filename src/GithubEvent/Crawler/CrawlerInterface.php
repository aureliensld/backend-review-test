<?php

namespace App\GithubEvent\Crawler;

use App\GithubEvent\Exception\NoEventFoundException;

interface CrawlerInterface
{
    /**
     * @param \DateTimeInterface $dateTime Crawls events within this specific date and hour
     *
     * @return iterable<array{
     *     id: int|string,
     *     type: string,
     *     payload: array{size?: int, ...},
     *     created_at: string,
     *     actor: array{id: int|string, login: string, url: string, avatar_url: string},
     *     repo: array{id: int|string, name: string, url: string}
     * }>
     *
     * @throws NoEventFoundException
     */
    public function run(\DateTimeInterface $dateTime): iterable;
}
