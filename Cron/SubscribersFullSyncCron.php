<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Cron;

use Psr\Log\LoggerInterface;
use Virtua\FreshMail\Model\FullSyncSubscriberService;

class SubscribersFullSyncCron
{
    /** @var FullSyncSubscriberService */
    private $fullSyncSubscriberService;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        FullSyncSubscriberService $fullSyncSubscriberService,
        LoggerInterface $logger
    ) {
        $this->fullSyncSubscriberService = $fullSyncSubscriberService;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $this->fullSyncSubscriberService->execute();
        } catch (\Throwable $t) {
            $this->logger->error('freshmail_subscribers_full_sync error', ['exception' => $t]);
        }
    }
}
