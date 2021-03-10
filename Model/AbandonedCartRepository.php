<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\AbandonedCartRepositoryInterface;
use Virtua\FreshMail\Api\Data\AbandonedCartInterface;
use Virtua\FreshMail\Api\Data\AbandonedCartInterfaceFactory;
use Virtua\FreshMail\Api\Data\AbandonedCartSearchResultsInterface;
use Virtua\FreshMail\Api\Data\AbandonedCartSearchResultsInterfaceFactory;
use Virtua\FreshMail\Model\ResourceModel\AbandonedCart as AbandonedCartResource;
use Virtua\FreshMail\Model\ResourceModel\AbandonedCart\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

class AbandonedCartRepository implements AbandonedCartRepositoryInterface
{
    /**
     * @var AbandonedCartInterface[]
     */
    private $instances = [];

    /**
     * @var AbandonedCartInterfaceFactory
     */
    private $abandonedCartFactory;

    /**
     * @var AbandonedCartResource
     */
    private $abandonedCartResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var AbandonedCartSearchResultsInterfaceFactory
     */
    private $abandonedCartSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        AbandonedCartInterfaceFactory $abandonedCartFactory,
        AbandonedCartResource $abandonedCartResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        AbandonedCartSearchResultsInterfaceFactory $abandonedCartSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->abandonedCartFactory = $abandonedCartFactory;
        $this->abandonedCartResource = $abandonedCartResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->abandonedCartSearchResultsFactory = $abandonedCartSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function save(AbandonedCartInterface $abandonedCart): void
    {
        try {
            $this->abandonedCartResource->save($abandonedCart);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save abandoned cart: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $abandonedCartId): AbandonedCartInterface
    {
        if (! isset($this->instances[$abandonedCartId][AbandonedCartInterface::CACHE_TAG])) {
            /** @var AbandonedCartInterface $abandonedCart */
            $abandonedCart = $this->abandonedCartFactory->create();
            $this->abandonedCartResource->load($abandonedCart, $abandonedCartId);
            if (! $abandonedCart->getId()) {
                throw NoSuchEntityException::singleField('entity_id', $abandonedCartId);
            }

            $this->instances[$abandonedCartId][AbandonedCartInterface::CACHE_TAG] = $abandonedCart;
        }

        return $this->instances[$abandonedCartId][AbandonedCartInterface::CACHE_TAG];
    }

    public function getByQuoteId(int $quoteId): AbandonedCartInterface
    {
        $abandonedCart = $this->abandonedCartFactory->create();
        $this->abandonedCartResource->load($abandonedCart, $quoteId, 'quote_id');
        if (! $abandonedCart->getId()) {
            throw NoSuchEntityException::singleField('quote_id', $quoteId);
        }

        return $abandonedCart;
    }

    /**
     *  {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AbandonedCartSearchResultsInterface
    {
        try {
            $collection = $this->collectionFactory->create();
            $this->collectionProcessor->process($searchCriteria, $collection);

            $searchResults = $this->abandonedCartSearchResultsFactory->create();
            $searchResults->setItems($collection->getItems());
            $searchResults->setSearchCriteria($searchCriteria);
            $searchResults->setTotalCount($collection->getSize());

        } catch (\Throwable $e) {
            $message = __('An error occurred during get follow up email list: %error', ['error' => $e->getMessage()]);
            throw new IntegrationException($message);
        }

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getAbandonedAtDate(string $date, int $storeId = null): AbandonedCartSearchResultsInterface
    { //todo change name to contain that we get not recovered cart
        $date = $this->stripDateFromTimePart($date);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('abandoned_at', $date . ' 00:00:00', 'gteq')
            ->addFilter('abandoned_at', $date . ' 23:59:59', 'lteq')
            ->addFilter('recovered', 0);

        if ($storeId) {
            $searchCriteria->addFilter('store_id', $storeId, 'eq');
        }

        return $this->getList($searchCriteria->create());
    }

    private function stripDateFromTimePart(string $date): string
    {
        $dateParts = explode(" ", $date);
        return $dateParts[0];
    }
}
