<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Virtua\FreshMail\Api\Data\RequestQueueSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class RequestQueueSearchResults extends SearchResults implements RequestQueueSearchResultsInterface
{
}