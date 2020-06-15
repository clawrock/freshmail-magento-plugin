<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Subscriber;
use Virtua\FreshMail\Api\SubscriberServiceInterface;
use Virtua\FreshMail\Api\SubscriberRepositoryInterface;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;

class SubscriberService implements SubscriberServiceInterface
{
    /**
     * @var SubscriberRepositoryInterface
     */
    private $subscriberRepository;

    /**
     * @var RequestQueueServiceInterface
     */
    private $requestQueueService;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        SubscriberRepositoryInterface $subscriberRepository,
        RequestQueueServiceInterface $requestQueueService,
        Config $config
    ) {
        $this->subscriberRepository = $subscriberRepository;
        $this->requestQueueService = $requestQueueService;
        $this->config = $config;
    }

    public function getSubscriberListHashByEmail(string $email): ?string
    {
        $listHash = null;
        try {
            $subscriber = $this->subscriberRepository->getByEmail($email);
            $listHash = $this->config->getListHashByStoreId($subscriber->getStoreId());
        } catch (NoSuchEntityException $e) {
            // todo handle exception
        }

        return $listHash;
    }
}