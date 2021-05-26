<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Filter\StripTags;
use Magento\Quote\Api\Data\CartInterface;
use Virtua\FreshMail\Api\AbandonedCartServiceInterface;
use Virtua\FreshMail\Api\AbandonedCartRepositoryInterface;
use Virtua\FreshMail\Api\Data\AbandonedCartInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Virtua\FreshMail\Model\System\FollowUpEmailAbandonedCartConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterfaceFactory;
use Virtua\FreshMail\Api\FollowUpEmailRepositoryInterface;

class ForceFollowUpEmails extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Virtua_FreshMail::config_freshmail';

    /**
     * @var StripTags
     */
    private $tagFilter;

    /**
     * @var Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var AbandonedCartRepositoryInterface
     */
    private $abandonedCartRepository;

    /**
     * @var AbandonedCartServiceInterface
     */
    private $abandonedCartService;

    /**
     * @var FollowUpEmailAbandonedCartConfig
     */
    private $followUpEmailAbandonedCartConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var FollowUpEmailInterfaceFactory
     */
    private $followUpEmailFactory;

    /**
     * @var FollowUpEmailRepositoryInterface
     */
    private $followUpEmailRepository;

    public function __construct(
        Context $context,
        StripTags $tagFilter,
        Result\JsonFactory $resultJsonFactory,
        AbandonedCartServiceInterface $abandonedCartService,
        AbandonedCartRepositoryInterface $abandonedCartRepository,
        FollowUpEmailAbandonedCartConfig $followUpEmailAbandonedCartConfig,
        StoreManagerInterface $storeManager,
        CartRepositoryInterface $cartRepository,
        FollowUpEmailInterfaceFactory $followUpEmailFactory,
        FollowUpEmailRepositoryInterface $followUpEmailRepository
    ) {
        parent::__construct($context);
        $this->tagFilter = $tagFilter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->abandonedCartService = $abandonedCartService;
        $this->abandonedCartRepository = $abandonedCartRepository;
        $this->followUpEmailAbandonedCartConfig = $followUpEmailAbandonedCartConfig;
        $this->storeManager = $storeManager;
        $this->cartRepository = $cartRepository;
        $this->followUpEmailFactory = $followUpEmailFactory;
        $this->followUpEmailRepository = $followUpEmailRepository;
    }

    public function execute(): Result\Json
    {
        $result = [
            'success' => false,
            'message' => '',
        ];

        $abandonedCarts = [];
        try {
            $carts = $this->abandonedCartService->findAbandonedCartsForDate($this->getTodayDate());
            foreach ($carts as $cart) {
                $abandonedCart = $this->abandonedCartService->createAbandonedCartInstanceFromQuote($cart);
                $abandonedCarts[] = $abandonedCart;
            }

            $this->addEmailsToQueueFromAbandonedCarts(
                $abandonedCarts,
                $this->followUpEmailAbandonedCartConfig->getFirstEmailTemplate($this->getDefaultStoreId()),
                FollowUpEmailInterface::TYPE_ABANDONED_FIRST
            );

            $result['success'] = true;

        } catch (CouldNotSaveException $e) {
            $result['success'] = true;
        } catch (\Throwable $e) {
            $result['message'] = $this->tagFilter->filter(__($e->getMessage()));
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }

    private function getTodayDate(): string
    {
        return date('Y-m-d', strtotime("now"));
    }

    /**
     * @param AbandonedCartInterface[] $abandonedCarts
     */
    private function addEmailsToQueueFromAbandonedCarts(array $abandonedCarts, int $templateId, string $emailType): void
    {
        foreach ($abandonedCarts as $cart) {
            $this->addEmailToQueue($cart, $templateId, $emailType);
        }
    }

    private function addEmailToQueue(AbandonedCartInterface $cart, int $templateId, string $emailType): void
    {
        try {
            $scheduledAt = $this->getScheduledAt();
            $quote = $this->getQuoteByAbandonedCart($cart);

            /** @var FollowUpEmailInterface $followUpEmail */
            $followUpEmail = $this->followUpEmailFactory->create();
            $followUpEmail->setCustomerId((int) $quote->getCustomer()->getId());
            $followUpEmail->setCustomerEmail($quote->getCustomer()->getEmail());
            $followUpEmail->setTemplateId($templateId);
            $followUpEmail->setScheduledAt($scheduledAt);
            $followUpEmail->setStoreId($this->getDefaultStoreId());
            $followUpEmail->setType($emailType);
            $followUpEmail->setConnectedEntityId((int) $quote->getId());

            $this->followUpEmailRepository->save($followUpEmail);

        } catch (\Throwable $e) {
        }
    }

    private function getQuoteByAbandonedCart(AbandonedCartInterface $cart): CartInterface
    {
        return $this->cartRepository->get($cart->getQuoteId());
    }

    private function getScheduledAt(): string
    {
        return date('Y-m-d H:i:s', strtotime("+5 minutes"));
    }

    private function getDefaultStoreId(): int
    {
        return (int) $this->storeManager->getDefaultStoreView()->getId();
    }
}
