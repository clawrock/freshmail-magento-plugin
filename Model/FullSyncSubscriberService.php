<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Exception;
use FreshMail\Api\Client\Exception\RequestException;
use FreshMail\Api\Client\Exception\ServerException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\Subscriber as MagentoSubscriber;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Api\FullSyncSubscriberServiceInterface;
use Virtua\FreshMail\Api\SubscriberRepositoryInterface;
use Virtua\FreshMail\Api\SubscriberListServiceInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Exception\ApiException;
use Virtua\FreshMail\Api\FreshMailStatusServiceInterface;
use Virtua\FreshMail\Api\RequestData\Subscriber;

class FullSyncSubscriberService implements FullSyncSubscriberServiceInterface
{
    /**
     * @var SubscriberRepositoryInterface
     */
    private $subscriberRepository;

    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;

    /**
     * @var FreshMailApiInterface
     */
    private $freshMailApi;

    /**
     * @var FreshMailStatusServiceInterface
     */
    private $statusService;

    /**
     * @var Subscriber\GetMultipleInterfaceFactory
     */
    private $subscriberGetMultipleFactory;

    /**
     * @var Subscriber\AddMultipleInterfaceFactory
     */
    private $subscriberAddMultipleFactory;

    /**
     * @var Subscriber\EditMultipleInterfaceFactory
     */
    private $subscriberEditMultipleFactory;

    /**
     * @var SubscriberListServiceInterface|null
     */
    private $subscriberListService = null;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $subscribersToAdd = [];

    /**
     * @var array
     */
    private $subscribersToEdit = [];

    /**
     * @var int
     */
    private $skippedNoNeedToUpdate = 0;

    /**
     * @var MagentoSubscriber[]
     */
    private $subscriberDataByEmail = [];

    /**
     * @var int
     */
    private $errors = 0;

    public function __construct(
        SubscriberRepositoryInterface $subscriberRepository,
        FreshMailApiInterfaceFactory $freshMailApiFactory,
        SubscriberListServiceInterface $subscriberListService,
        FreshMailStatusServiceInterface $statusService,
        Subscriber\GetMultipleInterfaceFactory $subscriberGetMultipleFactory,
        Subscriber\AddMultipleInterfaceFactory $subscriberAddMultipleFactory,
        Subscriber\EditMultipleInterfaceFactory $subscriberEditMultipleFactory,
        Config $config,
        Logger $logger
    ) {
        $this->subscriberRepository = $subscriberRepository;
        $this->freshMailApiFactory = $freshMailApiFactory; // todo this is probably not needed
        $this->freshMailApi = $freshMailApiFactory->create();
        $this->subscriberListService = $subscriberListService;
        $this->statusService = $statusService;
        $this->subscriberGetMultipleFactory = $subscriberGetMultipleFactory;
        $this->subscriberAddMultipleFactory = $subscriberAddMultipleFactory;
        $this->subscriberEditMultipleFactory = $subscriberEditMultipleFactory;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $subscriberLists = $this->subscriberRepository->getListByStore();
        $this->errors = 0;
        foreach ($subscriberLists as $storeId => $subscriberData) {
            try {
                $this->syncSubscribersFromStore($subscriberData, $storeId);
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
                $this->errors++;
            }
        }

        if ($this->errors) {
            throw new LocalizedException(__('Some errors have been occurred during FreshMail sync.'));
        }
    }

    /**
     * @throws ApiException
     * @throws LocalizedException
     * @throws RequestException
     * @throws ServerException
     */
    private function checkIfListExists(string $listHash, int $storeId): bool
    {
        if (empty($listHash)) {
            $message = __('Not configured hash list for given store id: %storeId', ['storeId' => $storeId]);
            throw new LocalizedException($message);
        }

        return $this->subscriberListService->hashListExists($listHash);
    }

    /**
     * @throws ApiException
     * @throws LocalizedException
     * @throws Exception
     */
    private function syncSubscribersFromStore(array $subscribers, ?int $storeId = null): void
    {
        $listHash = $this->config->getListHashByStoreId($storeId);
        if (! $this->checkIfListExists($listHash, $storeId)) {
            $message = __('Given list:  %listHash doest not exist on FreshMail account.', ['listHash' => $listHash]);
            throw new LocalizedException($message);
        }

        $index = 0;
        $lastIndex = count($subscribers);
        /** @var Subscriber\GetMultipleInterface $requestDataGetMultiple */
        $requestDataGetMultiple = $this->subscriberGetMultipleFactory->create(['list' => $listHash]);

        /** @var MagentoSubscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            $index++;
            $subscriberEmail = mb_strtolower($subscriber->getSubscriberEmail());
            try {
                $requestDataGetMultiple->addSubscriber($subscriberEmail);
                $this->subscriberDataByEmail[$subscriberEmail] = $subscriber;

                if (FreshMailApiInterface::API_REQUEST_LIMIT === $requestDataGetMultiple->getCount()) {
                    $this->checkSubscribersAndAssignToAction($requestDataGetMultiple);
                    $requestDataGetMultiple = $this->subscriberGetMultipleFactory->create(['list' => $listHash]);
                }
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());
                $this->errors++;
            }

            if ($index === $lastIndex && $requestDataGetMultiple->getCount() > 0) {
                $this->checkSubscribersAndAssignToAction($requestDataGetMultiple);
            }
        }

        $this->addSubscribersToFreshMail($listHash);
        $this->editSubscribersToFreshMail($listHash);
    }

    /**
     * @throws ApiException
     * @throws RequestException
     * @throws ServerException
     */
    private function checkSubscribersAndAssignToAction(Subscriber\GetMultipleInterface $requestData): void
    {
        try{
            $response = $this->freshMailApi->getMultipleSubscribers($requestData);
            $subscribersToAddCheck = $response->getDataErrors() ?? [];
            $subscribersToEditCheck = $response->getData() ?? [];
        } catch (\Exception $e) {
            if ($this->checkIfExceptionIsSubscribersMissing($e)) {
                $subscribersToAddCheck = $this->makeArrayForAdding($requestData->getSubscribers());
                $subscribersToEditCheck = [];
            } else {
                throw $e;
            }
        }
        
        $this->addToEdit($subscribersToEditCheck);
        $this->addToAdd($subscribersToAddCheck);
    }

    private function checkIfExceptionIsSubscribersMissing(\Exception $e): bool
    {
        $matches = [];
        preg_match('/"code":1311/', $e->getMessage(), $matches);
        return count($matches) ? true : false;
    }

    private function makeArrayForAdding(array $subscribers): array
    {
        $result = [];
        foreach ($subscribers as $subscriber) {
            $result[] = [
                'email' => $subscriber['email'],
                'code' => FreshMailApiInterface::ERROR_GET_SUBSCRIBER_NOT_EXISTS
            ];
        }

        return $result;
    }

    private function addToAdd(array $subscribersToAddCheck): void
    {
        try {
            foreach ($subscribersToAddCheck as $requestResult) {
                $code = (int) $requestResult['code'];
                if (FreshMailApiInterface::ERROR_GET_SUBSCRIBER_NOT_EXISTS === $code) {
                    $email = $requestResult['email'];
                    $subscriber = $this->subscriberDataByEmail[$email];
                    $magentoSubscriberStatus = (int) $subscriber->getSubscriberStatus();
                    $currentFreshMailStatus = $this->statusService->getFreshMailStatusBySubscriberStatus(
                        $magentoSubscriberStatus
                    );

                    $this->subscribersToAdd[$currentFreshMailStatus][] = [
                        'email' => $email,
                    ];
                } else {
                    throw new ApiException($requestResult['message'], $requestResult['code']);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            // todo think about throwing this error instead of loggin it only
        }
    }

    private function addToEdit(array $subscribersToEditCheck): void
    {
        try {
            // @todo: anything to do with subscriber "Soft bounce" and "Hard bounce" status (from the FreshMail)?
            foreach ($subscribersToEditCheck as $requestResult) {
                $email = $requestResult['email'];
                $subscriber = $this->subscriberDataByEmail[$email];
                $magentoSubscriberStatus = (int) $subscriber->getSubscriberStatus();
                $currentFreshMailStatus = $this->statusService->getFreshMailStatusBySubscriberStatus(
                    $magentoSubscriberStatus
                );

                $state = (int) $requestResult['state'];

                // check if need to be updated as there are not any custom fields
                if ($state === (int) $currentFreshMailStatus) {
                    $this->skippedNoNeedToUpdate++;
                    continue;
                }

                $this->subscribersToEdit[$currentFreshMailStatus][] = [
                    'email' => $email,
                ];
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @throws ApiException
     * @throws RequestException
     * @throws ServerException
     */
    private function addSubscribersToFreshMail(string $hashList): void
    {
        foreach ($this->subscribersToAdd as $freshMailStatus => $subscribersData) {
            $subscribersPerRequest = array_chunk(
                $subscribersData,
                FreshMailApiInterface::API_REQUEST_LIMIT,
                true
            );
            foreach ($subscribersPerRequest as $data) {
                try {
                    $addMultiple = $this->subscriberAddMultipleFactory->create([
                        'subscribers' => $data,
                        'list' => $hashList,
                        'state' => $freshMailStatus
                    ]);
                    $this->freshMailApi->addMultipleSubscribers($addMultiple);
                } catch (ApiException $exception) {
                    $this->errors++;
                    $this->logger->error($exception);
                }
            }
        }
    }

    /**
     * @throws ApiException
     * @throws RequestException
     * @throws ServerException
     */
    private function editSubscribersToFreshMail(string $hashList): void
    {
        foreach ($this->subscribersToEdit as $freshMailStatus => $subscribersData) {
            $subscribersPerRequest = array_chunk($subscribersData, FreshMailApiInterface::API_REQUEST_LIMIT, true);
            foreach ($subscribersPerRequest as $data) {
                try {
                    $editMultiple = $this->subscriberEditMultipleFactory->create([
                        'subscribers' => $data,
                        'list' => $hashList,
                        'state' => $freshMailStatus
                    ]);
                    $this->freshMailApi->editMultipleSubscribers($editMultiple);
                } catch (ApiException $exception) {
                    $this->errors++;
                    $this->logger->error($exception);
                }
            }
        }
    }
}
