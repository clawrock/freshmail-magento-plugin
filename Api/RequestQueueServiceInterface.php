<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Subscriber;
use Virtua\FreshMail\Api\Data\RequestQueueInterface;

interface RequestQueueServiceInterface
{
    public function addAddUserToQueue(
        string $email,
        int $storeId,
        ?int $magentoStatus = Subscriber::STATUS_SUBSCRIBED
    ): void;

    public function addEditUserToQueue(string $email, int $storeId, int $magentoStatus): void;

    public function addResignUserToQueue(string $email, int $storeId): void;

    public function addDeleteUserToQueue(string $email, int $storeId): void;

    public function addFullSyncToQueue(): void;

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function markRequestAsFailed(RequestQueueInterface $requestQueue): void;

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function markRequestAsSuccess(RequestQueueInterface $requestQueue): void;
}
