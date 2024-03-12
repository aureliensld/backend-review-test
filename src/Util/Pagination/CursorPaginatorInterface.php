<?php

namespace App\Util\Pagination;

/**
 * @extends \Traversable<int|string, mixed>
 */
interface CursorPaginatorInterface extends \Traversable
{
    public function getNextCursor(): int;
}
