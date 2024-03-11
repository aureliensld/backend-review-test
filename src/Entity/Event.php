<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'event', indexes: [new ORM\Index(columns: ['type'], name: 'IDX_EVENT_TYPE')])]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

    #[ORM\Column(type: 'EventType', nullable: false)]
    private string $type;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $count = 1;

    #[ORM\ManyToOne(targetEntity: Actor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'id', nullable: false)]
    private Actor $actor;

    #[ORM\ManyToOne(targetEntity: Repo::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'repo_id', referencedColumnName: 'id', nullable: false)]
    private Repo $repo;

    /**
     * @var mixed[]
     */
    #[ORM\Column(type: 'json', nullable: false, options: ['jsonb' => true])]
    private array $payload;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private \DateTimeImmutable $createAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment;

    /**
     * @param mixed[] $payload
     */
    public function __construct(int|string $id, string $type, Actor $actor, Repo $repo, array $payload, \DateTimeImmutable $createAt, ?string $comment)
    {
        $this->id = (string) $id;
        EventType::assertValidChoice($type);
        $this->type = $type;
        $this->actor = $actor;
        $this->repo = $repo;
        $this->payload = $payload;
        $this->createAt = $createAt;
        $this->comment = $comment;

        if (EventType::COMMIT === $type) {
            $this->count = $payload['size'] ?? 1;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function getRepo(): Repo
    {
        return $this->repo;
    }

    /**
     * @return mixed[]
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getCreateAt(): \DateTimeImmutable
    {
        return $this->createAt;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
