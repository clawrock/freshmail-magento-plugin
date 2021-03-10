<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface AbandonedCartSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return AbandonedCartInterface[]
     */
    public function getItems();

    /**
     * @param AbandonedCartInterface[] $items
     */
    public function setItems(array $items);
}
