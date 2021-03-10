<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\Data\AbandonedCartInterface;
use Virtua\FreshMail\Api\Data\AbandonedCartSearchResultsInterface;

interface AbandonedCartRepositoryInterface
{
    /**
     * @throws CouldNotSaveException
     */
    public function save(AbandonedCartInterface $abandonedCart): void;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $abandonedCartId): AbandonedCartInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getByQuoteId(int $quoteId): AbandonedCartInterface;

    /**
     * @throws IntegrationException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AbandonedCartSearchResultsInterface;

    /**
     * @throws IntegrationException
     */
    public function getAbandonedAtDate(string $date, int $storeId = null): AbandonedCartSearchResultsInterface;
}
