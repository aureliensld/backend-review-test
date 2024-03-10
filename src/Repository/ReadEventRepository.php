<?php

namespace App\Repository;

use App\Dto\SearchInput;

interface ReadEventRepository
{
    public function countAll(SearchInput $searchInput): int;

    /**
     * @return mixed[]
     */
    public function countByType(SearchInput $searchInput): array;

    /**
     * @return mixed[]
     */
    public function statsByTypePerHour(SearchInput $searchInput): array;

    /**
     * @return mixed[]
     */
    public function getLatest(SearchInput $searchInput): array;

    public function exist(int $id): bool;
}
