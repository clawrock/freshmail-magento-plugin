<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Quote\Api\Data\CartInterface;
use Virtua\FreshMail\Api\AbandonedCartServiceInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Virtua\FreshMail\Api\Data\AbandonedCartInterface;
use Virtua\FreshMail\Api\Data\AbandonedCartInterfaceFactory;

class AbandonedCartService implements AbandonedCartServiceInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AbandonedCartInterfaceFactory
     */
    private $abandonedCartFactory;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AbandonedCartInterfaceFactory $abandonedCartFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->abandonedCartFactory = $abandonedCartFactory;
    }

    /**
     * @inheritdoc
     */
    public function findAbandonedCartsForDateAndStore(string $date, int $storeId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', true)
            ->addFilter('store_id', $storeId)
            ->addFilter('updated_at', $date . ' 00:00:00', 'gteq')
            ->addFilter('updated_at', $date . ' 23:59:59', 'lteq')
            ->addFilter('customer_id', null, 'neq')
            ->create();

        return $this->cartRepository->getList($searchCriteria)->getItems();
    }

    public function findAbandonedCartsForDate(string $date): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', true)
            ->addFilter('updated_at', $date . ' 00:00:00', 'gteq')
            ->addFilter('updated_at', $date . ' 23:59:59', 'lteq')
            ->addFilter('customer_id', null, 'neq')
            ->create();

        return $this->cartRepository->getList($searchCriteria)->getItems();
    }

    public function createAbandonedCartInstanceFromQuote(CartInterface $cart): AbandonedCartInterface
    {
        /** @var AbandonedCartInterface $abandonedCart */
        $abandonedCart = $this->abandonedCartFactory->create();
        $abandonedCart->setQuoteId((int) $cart->getId());
        $abandonedCart->setAbandonedAt($cart->getUpdatedAt());
        $abandonedCart->setCartTotal((float) $cart->getSubtotal());
        $abandonedCart->setStoreId((int) $cart->getStoreId());

        return $abandonedCart;
    }
}
