<?php
// TODO think about adding a constraint into freshmail_follow_up table, so we do not get duplicated emails
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Model\Context;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Magento\Framework\Model\AbstractModel;
use Virtua\FreshMail\Model\ResourceModel\FollowUpEmail as FollowUpEmailResource;

class FollowUpEmail extends AbstractModel implements FollowUpEmailInterface
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(FollowUpEmailResource::class);
    }

    public function getCustomerId(): ?int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId($customerId)
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getCustomerEmail(): ?string
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    public function setCustomerEmail(string $customerEmail)
    {
        $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    public function getTemplateId(): ?int
    {
        return (int) $this->getData(self::TEMPLATE_ID);
    }

    public function setTemplateId($templateId)
    {
        $this->setData(self::TEMPLATE_ID, $templateId);
    }

    public function getStoreId(): ?int
    {
        return (int) $this->getData(self::STORE_ID);
    }

    public function setStoreId(int $storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
    }

    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getScheduledAt(): ?string
    {
        return $this->getData(self::SCHEDULED_AT);
    }

    public function setScheduledAt(string $scheduledAt): void
    {
        $this->setData(self::SCHEDULED_AT, $scheduledAt);
    }

    public function getSent(): bool
    {
        return (bool) $this->getData(self::SENT);
    }

    public function setSent(bool $sent): void
    {
        $this->setData(self::SENT, $sent);
    }

    public function getType(): string
    {
        return $this->getData(self::TYPE);
    }

    public function setType(string $emailType): void
    {
        $this->setData(self::TYPE, $emailType);
    }

    public function getConnectedEntityId(): ?int
    {
        return (int) $this->getData(self::CONNECTED_ENTITY_ID);
    }

    public function setConnectedEntityId(int $connectedEntityId)
    {
        $this->setData(self::CONNECTED_ENTITY_ID, $connectedEntityId);
    }
}
