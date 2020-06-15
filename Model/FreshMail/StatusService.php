<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail;

use Virtua\FreshMail\Api\FreshMailStatusServiceInterface;
use Magento\Newsletter\Model\Subscriber as Subscriber;

class StatusService implements FreshMailStatusServiceInterface
{
    /**
     * @return int[]
     */
    public static function allFreshMailSubscriberStatuses(): array
    {
        return [
            self::SUBSCRIBER_STATUS_SUBSCRIBED,
            self::SUBSCRIBER_STATUS_AWAITS_ACTIVATION,
            self::SUBSCRIBER_STATUS_NOT_ACTIVE,
            self::SUBSCRIBER_STATUS_UNSUBSCRIBED,
            self::SUBSCRIBER_STATUS_SOFT_BOUNCE,
            self::SUBSCRIBER_STATUS_HARD_BOUNCE,
        ];
    }

    public function getFreshMailStatusBySubscriberStatus(int $magentoStatus): int
    {
        switch ($magentoStatus) {
            case Subscriber::STATUS_SUBSCRIBED:
                $status = self::SUBSCRIBER_STATUS_SUBSCRIBED;
                break;
            case Subscriber::STATUS_NOT_ACTIVE:
            case Subscriber::STATUS_UNCONFIRMED:
                $status = self::SUBSCRIBER_STATUS_AWAITS_ACTIVATION;
                break;
            case Subscriber::STATUS_UNSUBSCRIBED:
                $status = self::SUBSCRIBER_STATUS_UNSUBSCRIBED;
                break;
            default:
                $status = self::SUBSCRIBER_STATUS_NOT_ACTIVE;
                break;
        }

        return $status;
    }
}
