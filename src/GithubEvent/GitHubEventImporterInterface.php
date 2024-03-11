<?php

namespace App\GithubEvent;

interface GitHubEventImporterInterface
{
    /**
     * Import GitHub event for a specific date and hour.
     *
     * @param \DateTimeInterface                        $date       Import events within this specific date and hour
     * @param callable(int $processedEvents): void|null $onProgress Callback to track processing. $processedEvents is the number of event processed so far
     *
     * @return int The number of imported events
     */
    public function import(\DateTimeInterface $date, ?callable $onProgress = null): int;
}
