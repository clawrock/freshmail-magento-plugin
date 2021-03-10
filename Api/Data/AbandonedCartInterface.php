<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api\Data;

interface AbandonedCartInterface
{
    public const ID = 'entity_id';
    public const QUOTE_ID = 'quote_id';
    public const CART_TOTAL = 'cart_total';
    public const RECOVERED = 'recovered';
    public const ABANDONED_AT = 'abandoned_at';
    public const RECOVERED_AT = 'recovered_at';
    public const STORE_ID = 'store_id';

    public const CACHE_TAG = 'abandoned_cart';

    /**
     * @return int|null
     */
    public function getEntityId();

    public function setEntityId(int $id);

    public function getQuoteId(): ?int;

    public function setQuoteId(int $quoteId);

    public function getCartTotal(): float;

    public function setCartTotal(float $total): void;

    public function getRecovered(): bool;

    public function setRecovered(bool $recovered): void;

    public function getAbandonedAt(): string;

    public function setAbandonedAt(string $abandonedAt): void;

    public function getRecoveredAt(): ?string;

    public function setRecoveredAt(string $recoveredAt): void;

    public function getStoreId(): ?int;

    public function setStoreId(int $storeId);
}
