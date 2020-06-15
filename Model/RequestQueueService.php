<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Virtua\FreshMail\Api\Data\RequestQueueInterface;
use Virtua\FreshMail\Api\Data\RequestQueueInterfaceFactory;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;
use Virtua\FreshMail\Api\RequestQueueRepositoryInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Api\SubscriberRepositoryInterface;
use Virtua\FreshMail\Api\FreshMailStatusServiceInterface;
use Magento\Newsletter\Model\Subscriber;

class RequestQueueService implements RequestQueueServiceInterface
{
    /**
     * @var RequestQueueInterfaceFactory
     */
    private $requestQueueInterfaceFactory;

    /**
     * @var RequestQueueRepositoryInterface
     */
    private $requestQueueRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubscriberRepositoryInterface
     */
    private $subscriberRepository;

    /**
     * @var FreshMailStatusServiceInterface
     */
    private $statusService;

    public function __construct(
        RequestQueueInterfaceFactory $requestQueueInterfaceFactory,
        RequestQueueRepositoryInterface $requestQueueRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Logger $logger,
        Config $config,
        SubscriberRepositoryInterface $subscriberRepository,
        FreshMailStatusServiceInterface $statusService
    ) {
        $this->requestQueueInterfaceFactory = $requestQueueInterfaceFactory;
        $this->requestQueueRepository = $requestQueueRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->config = $config;
        $this->subscriberRepository = $subscriberRepository;
        $this->statusService = $statusService;
    }

    public function addAddUserToQueue(
        string $email,
        int $storeId,
        ?int $magentoStatus = Subscriber::STATUS_SUBSCRIBED
    ): void {
        $this->addNewEntryToQueue(
            RequestQueueInterface::ACTION_ADD_USER,
            $this->getSubscriberRequestParams($email, $storeId, $magentoStatus)
        );
    }

    public function addEditUserToQueue(string $email, int $storeId, int $magentoStatus): void
    {
        $this->addNewEntryToQueue(
            RequestQueueInterface::ACTION_EDIT_USER,
            $this->getSubscriberRequestParams($email, $storeId, $magentoStatus)
        );
    }

    public function addResignUserToQueue(string $email, int $storeId): void
    {
        // TODo check if get subscriber params state filed is ok, maybe it should be hardoced to RESIGNED
        $this->addNewEntryToQueue(
            RequestQueueInterface::ACTION_RESIGN_USER,
            $this->getSubscriberRequestParams($email, $storeId, Subscriber::STATUS_UNSUBSCRIBED)
        );
    }

    public function addDeleteUserToQueue(string $email, int $storeId): void
    {
        $this->addNewEntryToQueue(
            RequestQueueInterface::ACTION_DELETE_USER,
            $this->getSubscriberRequestParams($email, $storeId)
        );
    }

    public function addFullSyncToQueue(): void
    {
        $this->addNewEntryToQueue(RequestQueueInterface::ACTION_FULL_SYNC_EMAILS, []);
    }

    private function getSubscriberRequestParams(string $email, int $storeId, ?int $magentoStatus = null): array
    {
        // todo think about handling scenario where there is no list hash
        $params =  [
            'list' => $this->config->getListHashByStoreId($storeId),
            'email' => mb_strtolower($email),
        ];

        if ($magentoStatus) {
            $params['state'] = $this->statusService->getFreshMailStatusBySubscriberStatus($magentoStatus);
        }

        return $params;
    }

    private function addNewEntryToQueue(int $actionType, array $params): void
    {
        try {
            $requestQueue = $this->requestQueueInterfaceFactory->create();
            $requestQueue->setParamsArray($params);
            $requestQueue->setActon($actionType);
            $requestQueue->setStatus(RequestQueueInterface::STATUS_PENDING);
            $this->requestQueueRepository->save($requestQueue);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            // todo think if we should handle somehow situation when we could not save request queue
        }
    }

    /**
     * {@inheritdoc}
     */
    public function markRequestAsFailed(RequestQueueInterface $requestQueue): void
    {
        $requestQueue->setStatus(RequestQueueInterface::STATUS_ERROR);
        $this->requestQueueRepository->save($requestQueue);
    }

    /**
     * {@inheritdoc}
     */
    public function markRequestAsSuccess(RequestQueueInterface $requestQueue): void
    {
        $requestQueue->setStatus(RequestQueueInterface::STATUS_SUCCESS);
        $this->requestQueueRepository->save($requestQueue);
    }
}
