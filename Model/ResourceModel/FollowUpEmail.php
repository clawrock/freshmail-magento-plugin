<?php
namespace Virtua\FreshMail\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db;

class FollowUpEmail extends Db\AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('freshmail_follow_up_email', 'entity_id');
    }
}
