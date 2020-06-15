<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use FreshMail\Api\Client\Exception\RequestException;
use FreshMail\Api\Client\Exception\ServerException;
use Magento\Framework\Exception\LocalizedException;
use Virtua\FreshMail\Exception\ApiException;

interface SubscriberListServiceInterface
{
    /**
     * @throws ApiException
     * @throws RequestException
     * @throws ServerException
     * @throws LocalizedException
     */
    public function getLists(): array;

    /**
     * @throws ApiException
     * @throws LocalizedException
     * @throws RequestException
     * @throws ServerException
     */
    public function hashListExists(string $hashList): bool;
}
