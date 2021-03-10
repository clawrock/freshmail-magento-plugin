<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Magento\Quote\Api\Data\CartInterface;
use Virtua\FreshMail\Api\Data\AbandonedCartInterface;

interface AbandonedCartServiceInterface
{
    /**
     * @return CartInterface[]
     */
    public function findAbandonedCartsForDateAndStore(string $date, int $storeId): array;

    /**
     * @return CartInterface[]
     */
    public function findAbandonedCartsForDate(string $date): array;

    public function createAbandonedCartInstanceFromQuote(CartInterface $cart): AbandonedCartInterface;
}
