<?php
namespace Virtua\FreshMail\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db;

class RequestQueue extends Db\AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('freshmail_request_queue', 'entity_id');
    }
}
