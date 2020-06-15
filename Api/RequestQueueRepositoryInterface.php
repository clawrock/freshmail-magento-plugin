<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\Data\RequestQueueInterface;
use Virtua\FreshMail\Api\Data\RequestQueueSearchResultsInterface;

interface RequestQueueRepositoryInterface
{
    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(RequestQueueInterface $requestQueue): void;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $requestQueueId): RequestQueueInterface;

    /**
     * @throws IntegrationException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): RequestQueueSearchResultsInterface;

    public function getListToProcess(?int $limit = null): RequestQueueSearchResultsInterface;
}
