<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Virtua\FreshMail\Api\SubscriberListServiceInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;

class SubscriberListService implements SubscriberListServiceInterface
{
    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;

    /**
     * @var FreshMailApiInterface
     */
    private $freshMailApi;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        FreshMailApiInterfaceFactory $freshMailApiFactory,
        FreshMailApiInterface $freshMailApi,
        Logger $logger
    ) {
        $this->freshMailApiFactory = $freshMailApiFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getLists(): array
    {
        return $this->getFreshMailApi()->getLists();
    }

    /**
     * {@inheritdoc}
     */
    public function hashListExists(string $hashList): bool
    {
        $lists = $this->getFreshMailApi()->getLists();
        foreach ($lists as $list) {
            if ($list['subscriberListHash'] === $hashList) {
                return true;
            }
        }

        return false;
    }

    private function getFreshMailApi(): FreshMailApiInterface
    {
        if (! $this->freshMailApi) {
            $this->freshMailApi = $this->freshMailApiFactory->create();
        }

        return $this->freshMailApi;
    }
}
