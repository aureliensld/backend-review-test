<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SearchInput
{
    /**
     * @var ?string
     */
    #[Assert\Date()]
    #[Assert\NotBlank(allowNull: true)]
    public $date;

    /**
     * @var ?string
     */
    #[Assert\NotBlank(allowNull: true)]
    public $keyword;

    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    public int $cursor = 0;

    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    #[Assert\LessThanOrEqual(200)]
    public int $resultsPerPage = 10;

    public function __construct()
    {
    }

    public function getDate(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d', $this->date ?? date('Y-m-d'));
    }
}
