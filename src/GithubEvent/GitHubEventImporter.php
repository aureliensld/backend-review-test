<?php

namespace App\GithubEvent;

use App\GithubEvent\Crawler\CrawlerInterface;
use App\Repository\WriteEventRepository;

class GitHubEventImporter implements GitHubEventImporterInterface
{
    public function __construct(
        private readonly CrawlerInterface $crawler,
        private readonly WriteEventRepository $repository,
    ) {
    }

    public function import(\DateTimeInterface $date, ?callable $onProgress = null): int
    {
        return $this->repository->bulkInsert($this->crawler->run($date), $onProgress);
    }
}
