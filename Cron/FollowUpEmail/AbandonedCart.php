<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Cron\FollowUpEmail;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Virtua\FreshMail\Api\Data\AbandonedCartInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterfaceFactory;
use Virtua\FreshMail\Api\FollowUpEmailRepositoryInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\FollowUpEmailAbandonedCartConfig;
use Virtua\FreshMail\Api\AbandonedCartServiceInterface;
use Virtua\FreshMail\Api\AbandonedCartRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class AbandonedCart
{
    /**
     * @var int|null
     */
    private $currentStoreId;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var FollowUpEmailAbandonedCartConfig
     */
    private $followUpEmailAbandonedCartConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FollowUpEmailRepositoryInterface
     */
    private $followUpEmailRepository;

    /**
     * @var FollowUpEmailInterfaceFactory
     */
    private $followUpEmailFactory;

    /**
     * @var AbandonedCartServiceInterface
     */
    private $abandonedCartService;

    /**
     * @var AbandonedCartRepositoryInterface
     */
    private $abandonedCartRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        DateTimeFactory $dateTimeFactory,
        FollowUpEmailAbandonedCartConfig $followUpEmailAbandonedCartConfig,
        FollowUpEmailRepositoryInterface $followUpEmailRepository,
        StoreManagerInterface $storeManager,
        AbandonedCartServiceInterface $abandonedCartService,
        FollowUpEmailInterfaceFactory $followUpEmailFactory,
        AbandonedCartRepositoryInterface $abandonedCartRepository,
        CartRepositoryInterface $cartRepository,
        Logger $logger
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->followUpEmailAbandonedCartConfig = $followUpEmailAbandonedCartConfig;
        $this->followUpEmailRepository = $followUpEmailRepository;
        $this->storeManager = $storeManager;
        $this->abandonedCartService = $abandonedCartService;
        $this->followUpEmailFactory = $followUpEmailFactory;
        $this->abandonedCartRepository = $abandonedCartRepository;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $this->setCurrentStoreId((int) $store->getId());
            $this->searchAndAddToQueueFirstEmailIfEnabled();
            $this->searchAndAddToQueueSecondEmailIfEnabled();
            $this->searchAndAddToQueueThirdEmailIfEnabled();
        }
    }

    private function setCurrentStoreId(int $storeId): void
    {
        $this->currentStoreId = $storeId;
    }

    private function getCurrentStoreId(): ?int
    {
        return $this->currentStoreId;
    }

    private function searchAndAddToQueueFirstEmailIfEnabled(): void
    {
        if ($this->followUpEmailAbandonedCartConfig->getIsFirstEmailEnabled($this->getCurrentStoreId())) {
            // todo we should only get abandoned carts that are not revoered yet - so we can schedule a follow up email
            $carts = $this->abandonedCartRepository->getAbandonedAtDate(
                $this->getDateByNumberOfDaysBack($this->followUpEmailAbandonedCartConfig->getFirstEmailSendAfter()),
                $this->getCurrentStoreId()
            );
            $this->addEmailsToQueueFromAbandonedCarts(
                $carts->getItems(),
                $this->followUpEmailAbandonedCartConfig->getFirstEmailTemplate($this->getCurrentStoreId()),
                FollowUpEmailInterface::TYPE_ABANDONED_FIRST
            );
        }
    }

    private function searchAndAddToQueueSecondEmailIfEnabled(): void
    {
        if ($this->followUpEmailAbandonedCartConfig->getIsSecondEmailEnabled($this->getCurrentStoreId())) {
            $carts = $this->abandonedCartRepository->getAbandonedAtDate(
                $this->getDateByNumberOfDaysBack($this->followUpEmailAbandonedCartConfig->getSecondEmailSendAfter()),
                $this->getCurrentStoreId()
            );
            $this->addEmailsToQueueFromAbandonedCarts(
                $carts->getItems(),
                $this->followUpEmailAbandonedCartConfig->getSecondEmailTemplate($this->getCurrentStoreId()),
                FollowUpEmailInterface::TYPE_ABANDONED_SECOND
            );
        }
    }

    private function searchAndAddToQueueThirdEmailIfEnabled(): void
    {
        if ($this->followUpEmailAbandonedCartConfig->getIsThirdEmailEnabled($this->getCurrentStoreId())) {
            $carts = $this->abandonedCartRepository->getAbandonedAtDate(
                $this->getDateByNumberOfDaysBack($this->followUpEmailAbandonedCartConfig->getThirdEmailSendAfter()),
                $this->getCurrentStoreId()
            );
            $this->addEmailsToQueueFromAbandonedCarts(
                $carts->getItems(),
                $this->followUpEmailAbandonedCartConfig->getThirdEmailTemplate($this->getCurrentStoreId()),
                FollowUpEmailInterface::TYPE_ABANDONED_THIRD
            );
        }
    }

    private function getDateByNumberOfDaysBack(int $daysBackFromToday): string
    {
        // todo optimize it
        $currentTime = $this->dateTimeFactory->create();
        $interval = new \DateInterval('P' . $daysBackFromToday . 'D');
        $searchDate = clone $currentTime;
        $searchDate->sub($interval);
        return $searchDate->format('Y-m-d');
    }

    /**
     * @param AbandonedCartInterface[] $abandonedCarts
     */
    private function addEmailsToQueueFromAbandonedCarts(array $abandonedCarts, int $templateId, string $emailType): void
    {
        //todo add email to queue on the same time it was abandoned
        foreach ($abandonedCarts as $cart) {
            $this->addEmailToQueue($cart, $templateId, $emailType);
        }
    }

    private function addEmailToQueue(AbandonedCartInterface $cart, int $templateId, string $emailType): void
    {
        try {
            $scheduledAt = $this->getScheduledAtBasedOnAbandonedAt($cart);
            // todo improve this instead of getting one quote from cart maybe some list?
            $quote = $this->getQuoteByAbandonedCart($cart);

            /** @var FollowUpEmailInterface $followUpEmail */
            $followUpEmail = $this->followUpEmailFactory->create();
            $followUpEmail->setCustomerId((int) $quote->getCustomer()->getId());
            $followUpEmail->setCustomerEmail($quote->getCustomer()->getEmail());
            $followUpEmail->setTemplateId($templateId);
            $followUpEmail->setScheduledAt($scheduledAt);
            $followUpEmail->setStoreId($this->getCurrentStoreId());
            $followUpEmail->setType($emailType);
            $followUpEmail->setConnectedEntityId((int) $quote->getId());

            $this->followUpEmailRepository->save($followUpEmail);

        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function getQuoteByAbandonedCart(AbandonedCartInterface $cart): CartInterface
    {
        // todo handle error
        return $this->cartRepository->get($cart->getQuoteId());
    }

    private function getScheduledAtBasedOnAbandonedAt(AbandonedCartInterface $cart): string
    {
        $abandonedAt = explode(" ", $cart->getAbandonedAt()); // todo think about making it look nicer
        $scheduledAt = $this->dateTimeFactory->create();

        return $scheduledAt->format('Y-m-d') . ' ' . $abandonedAt[1];
    }
}
