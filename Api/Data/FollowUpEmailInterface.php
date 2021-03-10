<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\Data;

interface FollowUpEmailInterface
{
    public const TYPE_ABANDONED_FIRST = 'abandoned_1';
    public const TYPE_ABANDONED_SECOND = 'abandoned_2';
    public const TYPE_ABANDONED_THIRD = 'abandoned_3';

    public const ID = 'entity_id';
    public const CUSTOMER_ID = 'customer_id';
    public const CUSTOMER_EMAIL = 'customer_email';
    public const TEMPLATE_ID = 'template_id';
    public const STORE_ID = 'store_id';
    public const NAME = 'name';
    public const CREATED_AT = 'created_at';
    public const SCHEDULED_AT = 'scheduled_at';
    public const SENT = 'sent';
    public const TYPE = 'type';
    public const CONNECTED_ENTITY_ID = 'connected_entity_id';

    public const CACHE_TAG = 'follow_up_email';

    /**
     * @return int|null
     */
    public function getEntityId();

    public function setEntityId(int $id);

    public function getCustomerId(): ?int;

    public function setCustomerId(int $customerId);

    public function getCustomerEmail(): ?string;

    public function setCustomerEmail(string $customerEmail);

    public function getTemplateId(): ?int;

    public function setTemplateId(int $templateId);

    public function getStoreId(): ?int;

    public function setStoreId(int $storeId);

    public function getCreatedAt(): string;

    public function setCreatedAt(string $createdAt): void;

    public function getScheduledAt(): ?string;

    public function setScheduledAt(string $processedAt): void;

    public function getSent(): bool;

    public function setSent(bool $sent): void;

    public function getType(): string;

    public function setType(string $emailType): void;

    public function getConnectedEntityId(): ?int;

    public function setConnectedEntityId(int $connectedEntityId);
}
