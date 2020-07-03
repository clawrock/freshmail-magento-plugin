<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\Flag;

use Magento\Framework\Flag as MagentoFlag;

class IntegrationActivationFlag extends MagentoFlag
{
    private const FRESHMAIL_INTEGRATION_IS_ACTIVATED = 'freshmail_integration_is_activated';

    protected $_flagCode = self::FRESHMAIL_INTEGRATION_IS_ACTIVATED;
}
