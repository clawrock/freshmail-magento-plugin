<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Magento\Framework\Exception\LocalizedException;
use Virtua\FreshMail\Exception\ApiException;

interface FullSyncSubscriberServiceInterface
{
    /**
     * @throws ApiException
     * @throws LocalizedException
     */
    public function execute(): void;
}
