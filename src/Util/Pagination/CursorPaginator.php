<?php

namespace App\Util\Pagination;

/**
 * @extends \ArrayIterator<int|string, mixed>
 */
class CursorPaginator extends \ArrayIterator implements CursorPaginatorInterface
{
    /**
     * @param mixed[] $array
     */
    public function __construct(array $array, private readonly ?int $nextCursor)
    {
        parent::__construct($array);
    }

    public function getNextCursor(): int
    {
        return $this->nextCursor;
    }
}
