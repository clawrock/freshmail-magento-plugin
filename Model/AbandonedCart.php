<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Model\AbstractModel;
use Virtua\FreshMail\Api\Data\AbandonedCartInterface;
use Virtua\FreshMail\Model\ResourceModel\AbandonedCart as AbandonedCartResource;

class AbandonedCart extends AbstractModel implements AbandonedCartInterface
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(AbandonedCartResource::class);
    }

    public function setQuoteId(int $quoteId)
    {
        $this->setData(self::QUOTE_ID, $quoteId);
    }

    public function getQuoteId(): ?int
    {
        return (int) $this->getData(self::QUOTE_ID);
    }

    public function setRecovered(bool $recovered): void
    {
        $this->setData(self::RECOVERED, $recovered);
    }

    public function getRecovered(): bool
    {
        return (bool) $this->getData(self::RECOVERED);
    }

    public function setCartTotal(float $total): void
    {
        $this->setData(self::CART_TOTAL, $total);
    }

    public function getCartTotal(): float
    {
        return (float) $this->getData(self::QUOTE_ID);
    }

    public function setAbandonedAt(string $abandonedAt): void
    {
        $this->setData(self::ABANDONED_AT, $abandonedAt);
    }

    public function getAbandonedAt(): string
    {
        return $this->getData(self::ABANDONED_AT);
    }

    public function setRecoveredAt(string $recoveredAt): void
    {
        $this->setData(self::RECOVERED_AT, $recoveredAt);
    }

    public function getRecoveredAt(): ?string
    {
        return $this->getData(self::RECOVERED_AT);
    }

    public function getStoreId(): ?int
    {
        return (int) $this->getData(self::STORE_ID);
    }

    public function setStoreId(int $storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
    }
}
