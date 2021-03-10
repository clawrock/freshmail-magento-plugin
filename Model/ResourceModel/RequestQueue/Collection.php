<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\ResourceModel\RequestQueue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Virtua\FreshMail\Model\RequestQueue;
use Virtua\FreshMail\Model\ResourceModel\RequestQueue as RequestQueueResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(
            RequestQueue::class,
            RequestQueueResource::class
        );
    }
}
