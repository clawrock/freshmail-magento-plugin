<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\Data;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Newsletter\Model\Subscriber;

interface SubscriberSearchResultsInterface
{
    /**
     * @return Subscriber[]
     */
    public function getItems(): array;

    /**
     * @param Subscriber[] $items
     */
    public function setItems(array $items): void;

    public function getSearchCriteria(): SearchCriteriaInterface;

    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria): void;

    public function getTotalCount(): int;

    public function setTotalCount(int $totalCount): void;
}
