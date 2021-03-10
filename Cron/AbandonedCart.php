<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Cron;

use Magento\Framework\Exception\CouldNotSaveException;
use Virtua\FreshMail\Api\AbandonedCartServiceInterface;
use Virtua\FreshMail\Api\AbandonedCartRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\Data\CartInterface;
use Virtua\FreshMail\Logger\Logger;

class AbandonedCart
{
    /**
     * @var AbandonedCartServiceInterface
     */
    private $abandonedCartService;

    /**
     * @var AbandonedCartRepositoryInterface
     */
    private $abandonedCartRepository;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        AbandonedCartServiceInterface $abandonedCartService,
        AbandonedCartRepositoryInterface $abandonedCartRepository,
        DateTime $dateTime,
        Logger $logger
    ) {
        $this->abandonedCartService = $abandonedCartService;
        $this->abandonedCartRepository = $abandonedCartRepository;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $this->findAndSaveAbandonedCarts();
    }

    private function findAndSaveAbandonedCarts(): void
    {
        $carts = $this->getAbandonedCartsFromYesterday();
        foreach ($carts as $cart) {
            $abandonedCart = $this->abandonedCartService->createAbandonedCartInstanceFromQuote($cart);
            try {
                $this->abandonedCartRepository->save($abandonedCart);
            } catch (CouldNotSaveException $e) {
                $this->logger->error('Could not save abandoned cart for quote_id - ' . $cart->getId());
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @return CartInterface[]
     */
    private function getAbandonedCartsFromYesterday(): array
    {
        return $this->abandonedCartService->findAbandonedCartsForDate($this->getYesterdayDate());
    }

    private function getYesterdayDate(): string
    {
        return date('Y-m-d', strtotime("-1 days"));
    }

}
