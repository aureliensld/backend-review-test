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

    public function __construct()
    {
    }

    public function getDate(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d', $this->date ?? date('Y-m-d'));
    }
}
