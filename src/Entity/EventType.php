<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * @extends AbstractEnumType<string, string>
 */
class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';
    public const COMMENT = 'MSG';
    public const PULL_REQUEST = 'PR';

    public const EVENT_TYPES = [
        'PushEvent' => self::COMMIT,
        'CommitCommentEvent' => self::COMMENT,
        'PullRequestReviewCommentEvent' => self::COMMENT,
        'PullRequestEvent' => self::PULL_REQUEST,
    ];

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];
}
