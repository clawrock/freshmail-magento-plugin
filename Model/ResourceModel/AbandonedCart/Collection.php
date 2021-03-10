<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\ResourceModel\AbandonedCart;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Virtua\FreshMail\Model\AbandonedCart;
use Virtua\FreshMail\Model\ResourceModel\AbandonedCart as AbandonedCartResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(
            AbandonedCart::class,
            AbandonedCartResource::class
        );
    }
}
