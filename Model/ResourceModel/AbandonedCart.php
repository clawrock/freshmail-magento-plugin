<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db;

class AbandonedCart extends Db\AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('freshmail_abandoned_cart', 'entity_id');
    }
}
