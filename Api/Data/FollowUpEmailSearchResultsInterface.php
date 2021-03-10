<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface FollowUpEmailSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return FollowUpEmailInterface[]
     */
    public function getItems();

    /**
     * @param FollowUpEmailInterface[] $items
     */
    public function setItems(array $items);
}
