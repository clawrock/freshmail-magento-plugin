<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResource;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection as SubscriberCollection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Virtua\FreshMail\Api\Data\SubscriberSearchResultsInterface;
use Virtua\FreshMail\Api\Data\SubscriberSearchResultsInterfaceFactory;
use Virtua\FreshMail\Api\SubscriberRepositoryInterface;

class SubscriberRepository implements SubscriberRepositoryInterface
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var SubscriberResource
     */
    private $subscriberResource;

    /**
     * @var CollectionFactory
     */
    private $newsletterSubscriberCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubscriberSearchResultsInterfaceFactory
     */
    private $subscriberSearchResultsInterfaceFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        SubscriberFactory $subscriberFactory,
        SubscriberResource $subscriberResource,
        CollectionFactory $newsletterSubscriberCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubscriberSearchResultsInterfaceFactory $subscriberSearchResultsInterfaceFactory,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->subscriberResource = $subscriberResource;
        $this->newsletterSubscriberCollectionFactory = $newsletterSubscriberCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subscriberSearchResultsInterfaceFactory = $subscriberSearchResultsInterfaceFactory;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $subscriberId): Subscriber
    {
        $subscriber = $this->subscriberFactory->create();
        $this->subscriberResource->load($subscriber, $subscriberId);
        if (! $subscriber->getId()) {
            throw NoSuchEntityException::singleField('subscriber_id', $subscriber);
        }

        return $subscriber;
    }
    
    public function getByEmail(string $email): Subscriber
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($email);
        if (! $subscriber->getId()) {
            throw NoSuchEntityException::singleField('subscriber_id', $subscriber);
        }

        return $subscriber;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SubscriberSearchResultsInterface
    {
        try {
            /** @var SubscriberCollection $collection */
            $collection = $this->newsletterSubscriberCollectionFactory->create();
            $this->collectionProcessor->process($searchCriteria, $collection);

            /** @var SubscriberSearchResultsInterface $subscriberSearchResults */
            $subscriberSearchResults = $this->subscriberSearchResultsInterfaceFactory->create();
            $subscriberSearchResults->setItems($collection->getItems());
            $subscriberSearchResults->setSearchCriteria($searchCriteria);
            $subscriberSearchResults->setTotalCount($collection->getSize());

            return $subscriberSearchResults;
        } catch (\Throwable $e) {
            $message = __('An error occurred during get subscriber newsletter list: %error', ['error' => $e->getMessage()]);
            throw new IntegrationException($message, $e);
        }
    }

    /**
     * @return StoreInterface[]
     *
     * @throws NoSuchEntityException
     */
    private function getStoreList(?int $storeId = null): array
    {
        $stores = [];
        if (null !== $storeId) {
            $stores[] = $this->storeManagerInterface->getStore($storeId);
        } else {
            $stores = $this->storeManagerInterface->getStores(false);
        }

        return $stores;
    }

    /**
     * {@inheritdoc}
     */
    public function getListByStore(?int $storeId = null): array
    {
        $subscribersListByStore = [];
        $stores = $this->getStoreList($storeId);

        foreach ($stores as $store) {
            $storeId = (int) $store->getId();
            $storeFilter = $this->getStoreFilter($storeId);
            $this->searchCriteriaBuilder->addFilters([$storeFilter]);

            $searchCriteria = $this->searchCriteriaBuilder->create();
            $subscriberSearchResults = $this->getList($searchCriteria);
            $subscribersListByStore[$storeId] = $subscriberSearchResults->getItems();
        }

        return $subscribersListByStore;
    }

    private function getStoreFilter(int $storeId): Filter
    {
        return $this->filterBuilder
            ->setField('store_id')
            ->setValue($storeId)
            ->setConditionType('eq')
            ->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribersNotUnsubscribed(int $fromLastId, ?int $limit = null): array
    {
        if (null !== $limit) {
            $this->searchCriteriaBuilder->setCurrentPage(1);
            $this->searchCriteriaBuilder->setPageSize($limit);
        }

        $this->searchCriteriaBuilder->addFilter('subscriber_status', Subscriber::STATUS_UNSUBSCRIBED, 'neq');
        $this->searchCriteriaBuilder->addFilter('subscriber_id', $fromLastId, 'gt');

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $subscriberSearchResults = $this->getList($searchCriteria);

        return $subscriberSearchResults->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function save(Subscriber $subscriber): void
    {
        try {
            $this->subscriberResource->save($subscriber);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save subscriber: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
    }
}
