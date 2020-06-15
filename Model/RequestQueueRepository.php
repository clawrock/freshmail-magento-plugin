<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\Data\RequestQueueInterface;
use Virtua\FreshMail\Api\Data\RequestQueueSearchResultsInterface;
use Virtua\FreshMail\Api\Data\RequestQueueSearchResultsInterfaceFactory;
use Virtua\FreshMail\Api\RequestQueueRepositoryInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\RequestQueue;
use Virtua\FreshMail\Model\ResourceModel\RequestQueue as RequestQueueResource;
use Virtua\FreshMail\Model\ResourceModel\RequestQueue\Collection as RequestQueueCollection;
use Virtua\FreshMail\Model\ResourceModel\RequestQueue\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;

class RequestQueueRepository implements RequestQueueRepositoryInterface
{
    /**
     * @var RequestQueue[]
     */
    protected $instances = [];

    /**
     * @var RequestQueueFactory
     */
    protected $requestQueueFactory;

    /**
     * @var RequestQueueResource
     */
    protected $requestQueueResource;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var RequestQueueSearchResultsInterfaceFactory
     */
    protected $requestQueueSearchResultsFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        RequestQueueFactory $requestQueueFactory,
        RequestQueueResource $requestQueueResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        RequestQueueSearchResultsInterfaceFactory $requestQueueSearchResultsFactory,
        Logger $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->requestQueueFactory = $requestQueueFactory;
        $this->requestQueueResource = $requestQueueResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->requestQueueSearchResultsFactory = $requestQueueSearchResultsFactory;
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestQueueInterface $requestQueue): void
    {
        try {
            $this->requestQueueResource->save($requestQueue);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save request queue: %1',
                    $e->getMessage()
                ),
                $e
            );
        }

        unset($this->instances[$requestQueue->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $requestQueueId): RequestQueueInterface
    {
        $cacheKey = 'freshmail_request_queue';

        if (! isset($this->instances[$requestQueueId][$cacheKey])) {
            /** @var RequestQueueInterface|RequestQueue $requestQueue */
            $requestQueue = $this->requestQueueFactory->create();
            $this->requestQueueResource->load($requestQueue, $requestQueueId);
            if (! $requestQueue->getId()) {
                throw NoSuchEntityException::singleField('entity_id', $requestQueue);
            }

            $this->instances[$requestQueueId][$cacheKey] = $requestQueue;
        }

        return $this->instances[$requestQueueId][$cacheKey];
    }

    /**
     *  {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria): RequestQueueSearchResultsInterface
    {
        try {
            /** @var RequestQueueCollection $collection */
            $collection = $this->collectionFactory->create();
            $this->collectionProcessor->process($searchCriteria, $collection);

            /** @var RequestQueueSearchResultsInterface $searchResults */
            $searchResults = $this->requestQueueSearchResultsFactory->create();
            $searchResults->setItems($collection->getItems());
            $searchResults->setSearchCriteria($searchCriteria);
            $searchResults->setTotalCount($collection->getSize());

        } catch (\Throwable $e) {
            $message = __('An error occurred during get request queue list: %error', ['error' => $e->getMessage()]);
            //throw new IntegrationException($message, $e);
            throw new IntegrationException($message);
        }

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getListToProcess(?int $limit = null): RequestQueueSearchResultsInterface
    {
        if (null !== $limit) {
            $this->searchCriteriaBuilder->setCurrentPage(1);
            $this->searchCriteriaBuilder->setPageSize($limit);
        }

        $filter = $this->filterBuilder
            ->setField(RequestQueueInterface::STATUS)
            ->setValue(RequestQueueInterface::STATUS_PENDING)
            ->setConditionType('eq')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->getList($searchCriteria);
    }
}
