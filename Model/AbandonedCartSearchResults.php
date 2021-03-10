<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Api\SearchResults;
use Virtua\FreshMail\Api\Data\AbandonedCartSearchResultsInterface;

class AbandonedCartSearchResults extends SearchResults implements AbandonedCartSearchResultsInterface
{
}
