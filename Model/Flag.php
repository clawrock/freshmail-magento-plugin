<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Flag as MagentoFlag;

class Flag extends MagentoFlag
{
    public const REPORT_ABANDONED_CART_FLAG_CODE = 'report_abandoned_cart_aggregated';

    public const SYNC_FROM_FRESHMAIL_LAST_SUBSCRIBER_ID = 'sync_from_freshmail_last_subscriber_id';

    public function setFreshMailFlagCode($code): void
    {
        $this->_flagCode = $code;
    }
}
