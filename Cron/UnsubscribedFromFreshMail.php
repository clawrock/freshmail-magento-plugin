<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Cron;

use Exception;
use FreshMail\Api\Client\Exception\RequestException;
use FreshMail\Api\Client\Exception\ServerException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Flag\FlagResource;
use Magento\Newsletter\Model\Subscriber as MagentoSubscriber;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Virtua\FreshMail\Api\SubscriberRepositoryInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Exception\ApiException;
use Virtua\FreshMail\Model\Flag;
use Virtua\FreshMail\Model\FreshMail\StatusService;
use Virtua\FreshMail\Api\RequestData\Subscriber;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;
use Virtua\FreshMail\Api\FreshMailApiInterface;

class UnsubscribedFromFreshMail
{
    private const SYNC_BATCH_LIMIT = 100;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Flag
     */
    private $flagAt;

    /**
     * @var FlagResource
     */
    private $flagResource;

    /**
     * @var SubscriberRepositoryInterface
     */
    private $subscriberRepository;

    /**
     * @var Subscriber\GetMultipleFactory
     */
    private $getMultipleFactory;

    /**
     * @var array
     */
    private $subscriberDataByEmail = [];

    /**
     * @var int
     */
    private $errors = 0;

    /**
     * @var array|null
     */
    private $freshMailListByStore = null;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;

    /**
     * @var FreshMailApiInterface
     */
    private $freshMailApi;

    /**
     * @throws LocalizedException
     */
    public function __construct(
        Config $config,
        Flag $flag,
        FlagResource $flagResource,
        SubscriberRepositoryInterface $subscriberRepository,
        Subscriber\GetMultipleInterfaceFactory $getMultipleFactory,
        StoreManagerInterface $storeManagerInterface,
        Logger $logger,
        FreshMailApiInterfaceFactory $freshMailApiFactory
    ) {
        $this->config = $config;
        $flag->setFreshMailFlagCode(Flag::SYNC_FROM_FRESHMAIL_LAST_SUBSCRIBER_ID);
        $this->flagAt = $flag->loadSelf();
        $this->flagResource = $flagResource;
        $this->subscriberRepository = $subscriberRepository;
        $this->getMultipleFactory = $getMultipleFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->logger = $logger;
        $this->freshMailApiFactory = $freshMailApiFactory;
    }

    private function getLastSyncFlagId(): int
    {
        if ($this->flagAt->hasData('flag_data')) {
            return $this->flagAt->getFlagData();
        }

        return 0;
    }

    /**
     * @throws AlreadyExistsException
     */
    private function saveLastSyncId(int $lastId): void
    {
        $this->flagAt->setFlagData($lastId);
        $this->flagResource->save($this->flagAt);
    }

    public function execute(): void
    {
        try {
            $subscribersToProcess = $this->subscriberRepository->getSubscribersNotUnsubscribed(
                $this->getLastSyncFlagId(),
                self::SYNC_BATCH_LIMIT
            );

            if (count($subscribersToProcess) < 1) {
                $this->saveLastSyncId(0);
                $subscribersToProcess = $this->subscriberRepository->getSubscribersNotUnsubscribed(
                    $this->getLastSyncFlagId(),
                    self::SYNC_BATCH_LIMIT
                );
            }

            $this->processStoreSyncFromFreshMail($subscribersToProcess);
        } catch (\Throwable $exception) {
            echo $exception->getMessage();
            $this->logger->error($exception->getMessage());
            $this->errors++;
        }
    }

    private function getFreshMailListByStore(int $fromStoreId): string
    {
        if (null === $this->freshMailListByStore) {
            $stores = $this->getStoreList();
            foreach ($stores as $store) {
                $storeId = (int) $store->getId();
                $this->freshMailListByStore[$storeId] = $this->config->getListHashByStoreId($storeId);
            }
        }

        return $this->freshMailListByStore[$fromStoreId];
    }

    /**
     * @return StoreInterface[]
     */
    private function getStoreList(): array
    {
        return $this->storeManagerInterface->getStores(false);
    }

    /**
     * @throws ApiException
     * @throws RequestException
     * @throws ServerException
     * @throws Exception
     */
    public function processStoreSyncFromFreshMail(array $subscribers): void
    {
        $index = 0;
        $lastIndex = count($subscribers);
        $storeList = $this->getStoreList();
        $requestDataGetMultipleByListHash = [];

        foreach ($storeList as $store) {
            $storeId = (int) $store->getId();
            $listHash = $this->getFreshMailListByStore($storeId);
            /** @var Subscriber\GetMultipleInterface $requestDataGetMultiple */
            $requestDataGetMultiple = $this->getMultipleFactory->create(['list' => $listHash]);
            $requestDataGetMultipleByListHash[$listHash] = $requestDataGetMultiple;
        }

        /** @var MagentoSubscriber $magentoSubscriber */
        foreach ($subscribers as $magentoSubscriber) {
            $index++;
            $storeId = (int) $magentoSubscriber->getStoreId();
            $listHash = $this->getFreshMailListByStore($storeId);
            $subscriberId = (int) $magentoSubscriber->getId();
            $subscriberEmail = mb_strtolower($magentoSubscriber->getSubscriberEmail());

            try {
                $requestDataGetMultipleByListHash[$listHash]->addSubscriber($subscriberEmail);
                $this->subscriberDataByEmail[$subscriberEmail] = $magentoSubscriber;

                if (FreshMailApiInterface::API_REQUEST_LIMIT === $requestDataGetMultipleByListHash[$listHash]->getCount()) {
                    $this->processList($requestDataGetMultipleByListHash[$listHash]);
                    $this->saveLastSyncId($subscriberId);
                    $requestDataGetMultipleByListHash[$listHash] = $this->getMultipleFactory->create([
                        'list' => $listHash
                    ]);

                }
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());
            }

            if ($index === $lastIndex) {
                foreach ($requestDataGetMultipleByListHash as $requestDataGetMultiple) {
                    if ($requestDataGetMultiple->getCount() > 0) {
                        $this->processList($requestDataGetMultiple);
                    }
                }
                $this->saveLastSyncId($subscriberId);
            }
        }
    }


    private function processList(Subscriber\GetMultipleInterface $getMultiple): void
    {
        $response = $this->getFreshMailApi()->getMultipleSubscribers($getMultiple);

        try {
            $this->unsubscribeFromMagento($response->getData()); // todo think about handling error response data - for example when subscriber was removed from freshmail side
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function unsubscribeFromMagento(array $requestSubscriberData): void
    {
        foreach ($requestSubscriberData as $requestResult) {
            try {
                $email = $requestResult['email'];
                $state = (int) $requestResult['state'];
                if (StatusService::SUBSCRIBER_STATUS_UNSUBSCRIBED !== $state) {
                    unset($this->subscriberDataByEmail[$email]);
                    continue;
                }

                $subscriber = $this->subscriberDataByEmail[$email];
                $subscriber->setStatus(MagentoSubscriber::STATUS_UNSUBSCRIBED);
                $this->subscriberRepository->save($subscriber);
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());
                $this->errors++;
            }
        }
    }

    private function getFreshMailApi(): FreshMailApiInterface
    {
        if (! $this->freshMailApi) {
            $this->freshMailApi = $this->freshMailApiFactory->create();
        }

        return $this->freshMailApi;
    }
}
