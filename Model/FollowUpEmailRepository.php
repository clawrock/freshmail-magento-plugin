<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterfaceFactory;
use Virtua\FreshMail\Api\Data\FollowUpEmailSearchResultsInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailSearchResultsInterfaceFactory;
use Virtua\FreshMail\Api\FollowUpEmailRepositoryInterface;
use Virtua\FreshMail\Model\ResourceModel\FollowUpEmail as FollowUpEmailResource;
use Virtua\FreshMail\Model\ResourceModel\FollowUpEmail\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;

class FollowUpEmailRepository implements FollowUpEmailRepositoryInterface
{
    /**
     * @var FollowUpEmailInterface[]
     */
    private $instances = [];

    /**
     * @var FollowUpEmailInterfaceFactory
     */
    private $followUpEmailFactory;

    /**
     * @var FollowUpEmailResource
     */
    private $followUpEmailResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var FollowUpEmailSearchResultsInterfaceFactory
     */
    private $followUpEmailSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    public function __construct(
        FollowUpEmailInterfaceFactory $followUpEmailFactory,
        FollowUpEmailResource $followUpEmailResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        FollowUpEmailSearchResultsInterfaceFactory $followUpEmailSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->followUpEmailFactory = $followUpEmailFactory;
        $this->followUpEmailResource = $followUpEmailResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->followUpEmailSearchResultsFactory = $followUpEmailSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(FollowUpEmailInterface $followUpEmail): void
    {
        try {
            $this->followUpEmailResource->save($followUpEmail);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save follow up email: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $followUpEmailId): FollowUpEmailInterface
    {
        if (! isset($this->instances[$followUpEmailId][FollowUpEmailInterface::CACHE_TAG])) {
            /** @var FollowUpEmailInterface $followUpEmail */
            $followUpEmail = $this->followUpEmailFactory->create();
            $this->followUpEmailResource->load($followUpEmail, $followUpEmailId);
            if (! $followUpEmail->getId()) {
                throw NoSuchEntityException::singleField('entity_id', $followUpEmailId);
            }

            $this->instances[$followUpEmailId][FollowUpEmailInterface::CACHE_TAG] = $followUpEmail;
        }

        return $this->instances[$followUpEmailId][FollowUpEmailInterface::CACHE_TAG];
    }

    /**
     *  {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FollowUpEmailSearchResultsInterface
    {
        try {
            $collection = $this->collectionFactory->create();
            $this->collectionProcessor->process($searchCriteria, $collection);

            $searchResults = $this->followUpEmailSearchResultsFactory->create();
            $searchResults->setItems($collection->getItems());
            $searchResults->setSearchCriteria($searchCriteria);
            $searchResults->setTotalCount($collection->getSize());

        } catch (\Throwable $e) {
            $message = __('An error occurred during get follow up email list: %error', ['error' => $e->getMessage()]);
            //throw new IntegrationException($message, $e);
            throw new IntegrationException($message);
        }

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getScheduledEmails(): array
    {
        //try {
            $now = $this->dateTimeFactory->create()->format('Y-m-d H:i:s');
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(FollowUpEmailInterface::SENT, 0)
                ->addFilter(FollowUpEmailInterface::SCHEDULED_AT, $now, 'lteq')
                ->create();

            $collection = $this->collectionFactory->create();
            $this->collectionProcessor->process($searchCriteria, $collection);

            $searchResults = $this->followUpEmailSearchResultsFactory->create();
            $searchResults->setItems($collection->getItems());
            $searchResults->setSearchCriteria($searchCriteria);
            $searchResults->setTotalCount($collection->getSize());

        /*} catch (\Throwable $e) {

        }*/
        return $searchResults->getItems();
    }
}
