<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

interface FreshMailStatusServiceInterface
{
    public const SUBSCRIBER_STATUS_SUBSCRIBED = 1;
    public const SUBSCRIBER_STATUS_AWAITS_ACTIVATION = 2;
    public const SUBSCRIBER_STATUS_NOT_ACTIVE = 3;
    public const SUBSCRIBER_STATUS_UNSUBSCRIBED = 4;
    public const SUBSCRIBER_STATUS_SOFT_BOUNCE = 5;
    public const SUBSCRIBER_STATUS_HARD_BOUNCE = 8;

    public function getFreshMailStatusBySubscriberStatus(int $magentoStatus): int;

    /**
     * @return int[]
     */
    public static function allFreshMailSubscriberStatuses(): array;
}