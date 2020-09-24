<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Exception;
use FreshMail\Api\Client\Exception\ClientException;
use FreshMail\Api\Client\Exception\RequestException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Virtua\FreshMail\Exception\ApiException;

interface IntegrationServiceInterface
{
    /**
     * @throws Exception
     * @throws AlreadyExistsException
     * @throws ApiException
     * @throws LocalizedException
     * @throws ClientException
     * @throws RequestException
     */
    public function initIntegration(): void;

    public function isIntegrationNeeded(): bool;
}
