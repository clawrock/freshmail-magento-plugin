<?php
// TODO check if it is needed
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Newsletter\Model\Subscriber as MagentoSubscriber;
use Virtua\FreshMail\Api\Data\SubscriberSearchResultsInterface;

class SubscriberSearchResults implements SubscriberSearchResultsInterface
{
    protected const KEY_ITEMS = 'items';
    protected const KEY_SEARCH_CRITERIA = 'search_criteria';
    protected const KEY_TOTAL_COUNT = 'total_count';

    /**
     * @var MagentoSubscriber[]|null
     */
    protected $items = null;

    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * @var int
     */
    protected $totalCount = 0;

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return null === $this->items ? [] : $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria(): SearchCriteriaInterface
    {
        return $this->searchCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria): void
    {
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount(int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    public function getData(): array
    {
        return [
            self::KEY_ITEMS => $this->getItems(),
            self::KEY_SEARCH_CRITERIA => $this->getSearchCriteria(),
            self::KEY_TOTAL_COUNT => $this->getTotalCount(),
        ];
    }
}
