<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="repo")
 */
class Repo
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(type="bigint")
     *
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $id;

    /**
     * @ORM\Column(type="string")
     */
    public string $name;

    /**
     * @ORM\Column(type="string")
     */
    public string $url;

    public function __construct(int|string $id, string $name, string $url)
    {
        $this->id = (string) $id;
        $this->name = $name;
        $this->url = $url;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): string
    {
        return $this->url;
    }
}
