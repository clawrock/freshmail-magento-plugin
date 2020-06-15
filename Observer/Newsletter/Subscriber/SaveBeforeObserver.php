<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Observer\Newsletter\Subscriber;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\Newsletter\Model\Subscriber;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Api\SubscriberRepositoryInterface;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;
use Virtua\FreshMail\Model\FreshMail\StatusService;
use Virtua\FreshMail\Api\SubscriberServiceInterface;

class SaveBeforeObserver implements ObserverInterface
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

        if ($subscriber->getId()) {
            $origSubscriber = $this->subscriberRepository->getById((int) $subscriber->getId());

            // we have to set original data to get the changed values in save after observer
            $subscriber->setOrigData('subscriber_email', $origSubscriber->getEmail())
                ->setOrigData('subscriber_status', $origSubscriber->getStatus())
                ->setOrigData('store_id', $origSubscriber->getStoreId());
        } else {
            $this->processNewSubscriber($subscriber);
        }
    }

    private function processNewSubscriber(Subscriber $subscriber): void
    {
        $this->requestQueueService->addAddUserToQueue(
            $subscriber->getEmail(),
            (int) $subscriber->getStoreId(),
            (int) $subscriber->getStatus()
        );
    }
}
