<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Observer\Newsletter\Subscriber;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Api\SubscriberRepositoryInterface;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;
use Virtua\FreshMail\Model\FreshMail\StatusService;
use Virtua\FreshMail\Api\SubscriberServiceInterface;

class SaveAfterObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var SubscriberRepositoryInterface
     */
    private $subscriberRepository;

    /**
     * @var RequestQueueServiceInterface
     */
    private $requestQueueService;

    /**
     * @var StatusService
     */
    private $status;

    /**
     * @var SubscriberServiceInterface
     */
    private $subscriberService;

    public function __construct(
        Config $config,
        Logger $logger,
        SubscriberRepositoryInterface $subscriberRepository,
        RequestQueueServiceInterface $requestQueueService,
        StatusService $status,
        SubscriberServiceInterface $subscriberService
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->subscriberRepository = $subscriberRepository;
        $this->requestQueueService = $requestQueueService;
        $this->status = $status;
        $this->subscriberService = $subscriberService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer): void
    {
        if (! $this->config->isEnabled()) {
            return;
        }

        /** @var Subscriber $subscriber */
        $subscriber = $observer->getSubscriber();

        if ($subscriber->hasDataChanges()) {
            $this->processChangedSubscriber($subscriber);
        }

    }

    private function processChangedSubscriber(Subscriber $subscriber): void
    {
        $statusHasChanged = (int) $subscriber->getStatus() !== (int) $subscriber->getOrigData('subscriber_status');
        $emailHasChanged = (string) $subscriber->getEmail() !== (string) $subscriber->getOrigData('subscriber_email');

        if (! $statusHasChanged && ! $emailHasChanged) {
            return;
        }

        if ($emailHasChanged) {
            $this->processSubscriberChangedEmail($subscriber);
        } elseif ($statusHasChanged) {
            $this->processSubscriberChangedStatus($subscriber);
        }
    }

    private function processSubscriberChangedEmail(Subscriber $subscriber): void
    {
        switch ($subscriber->getOrigData('subscriber_status')) {
            case Subscriber::STATUS_SUBSCRIBED:
                $this->requestQueueService->addResignUserToQueue(
                    $subscriber->getOrigData('subscriber_email'),
                    (int) $subscriber->getStoreId()
                );
                $this->requestQueueService->addAddUserToQueue(
                    $subscriber->getEmail(),
                    (int) $subscriber->getStoreId()
                );
                break;
            case Subscriber::STATUS_NOT_ACTIVE:
            case Subscriber::STATUS_UNCONFIRMED:
            case Subscriber::STATUS_UNSUBSCRIBED:
                $this->requestQueueService->addDeleteUserToQueue(
                    $subscriber->getOrigData('subscriber_email'),
                    (int) $subscriber->getStoreId()
                );
                $this->requestQueueService->addAddUserToQueue(
                    $subscriber->getEmail(),
                    (int) $subscriber->getStoreId(),
                    (int) $subscriber->getStatus()
                );
                break;
        }
    }

    private function processSubscriberChangedStatus(Subscriber $subscriber): void
    {
        $this->requestQueueService->addEditUserToQueue(
            $subscriber->getEmail(),
            (int) $subscriber->getStoreId(),
            (int) $subscriber->getStatus()
        );
    }
}
