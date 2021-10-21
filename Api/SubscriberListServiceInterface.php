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
     * @param string|null $token
     * @return array
     * @throws ApiException
     * @throws RequestException
     * @throws ServerException
     * @throws LocalizedException
     */
    public function getLists(?string $token = null): array;

    /**
     * @throws ApiException
     * @throws LocalizedException
     * @throws RequestException
     * @throws ServerException
     */
    public function hashListExists(string $hashList): bool;
}
