<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Subscriber;
use Virtua\FreshMail\Api\Data\SubscriberSearchResultsInterface;

interface SubscriberRepositoryInterface
{
    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriberId): Subscriber;

    /**
     * @throws NoSuchEntityException
     */
    public function getByEmail(string $email): Subscriber;

    /**
     * @throws IntegrationException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SubscriberSearchResultsInterface;

    /**
     * Returns data:
     *  array(
     *   0 => array(
     *      int store_id => Subscriber[]
     * )
     *
     * @throws IntegrationException
     * @throws NoSuchEntityException
     */
    public function getListByStore(?int $storeId = null): array;

    /**
     * @return Subscriber[]
     *
     * @throws IntegrationException
     * @throws NoSuchEntityException
     */
    public function getSubscribersNotUnsubscribed(int $fromLastId, ?int $limit = null): array;

    /**
     * @throws CouldNotSaveException
     */
    public function save(Subscriber $subscriber): void;
}
