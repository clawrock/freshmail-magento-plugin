<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\ResourceModel\FollowUpEmail;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Virtua\FreshMail\Model\FollowUpEmail;
use Virtua\FreshMail\Model\ResourceModel\FollowUpEmail as FollowUpEmailResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(
            FollowUpEmail::class,
            FollowUpEmailResource::class
        );
    }
}
