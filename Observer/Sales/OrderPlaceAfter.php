<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Virtua\FreshMail\Api\AbandonedCartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Virtua\FreshMail\Logger\Logger;
use Magento\Framework\Stdlib\DateTime\DateTime;

class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var AbandonedCartRepositoryInterface
     */
    private $abandonedCartRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DateTime
     */
    private $date;

    public function __construct(
        AbandonedCartRepositoryInterface $abandonedCartRepository,
        Logger $logger,
        DateTime $date
    ) {
        $this->abandonedCartRepository = $abandonedCartRepository;
        $this->logger = $logger;
        $this->date = $date;
    }

    public function execute(Observer $observer): void
    {
        /** @var CartInterface $order */
        $order = $observer->getData('order');
        try {
            $abandonedCart = $this->abandonedCartRepository->getByQuoteId((int) $order->getQuoteId());
        } catch(NoSuchEntityException $e) {
            return;
        }

        $abandonedCart->setRecovered(true);
        $abandonedCart->setRecoveredAt($this->date->gmtDate());

        try {
            $this->abandonedCartRepository->save($abandonedCart);
        } catch (CouldNotSaveException $e) {
            $this->logger->error(
                'Could not set abandoned cart with id - ' . $abandonedCart->getEntityId() . ' as recovered'
            );
            $this->logger->error($e->getMessage());
        }
    }
}
