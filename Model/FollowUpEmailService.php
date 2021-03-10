<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use \Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\Email\SenderInterface;
use Virtua\FreshMail\Api\FollowUpEmailServiceInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Virtua\FreshMail\Api\Email\SenderFactoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Virtua\FreshMail\Api\TemplateRepositoryInterface;

class FollowUpEmailService implements FollowUpEmailServiceInterface
{
    /**
     * @var FollowUpEmailInterface|null
     */
    private $followUpEmail;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SenderFactoryInterface
     */
    private $senderFactory;

    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    public function __construct(
        SenderFactoryInterface $senderFactory,
        CartRepositoryInterface $cartRepository,
        TemplateRepositoryInterface $templateRepository
    ) {
        $this->senderFactory = $senderFactory;
        $this->cartRepository = $cartRepository;
        $this->templateRepository = $templateRepository;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getEmailSenderForFollowUpEmail(FollowUpEmailInterface $email): SenderInterface
    {
        try {
            $sender = $this->senderFactory->create($email);
            $this->setFollowUpEmail($email);
            $this->setVariablesToSender($sender);
        } catch (Exception $e) {
            // todo handle exception
        }

        return $sender;
    }

    private function setFollowUpEmail(FollowUpEmailInterface $email): void
    {
        $this->followUpEmail = $email;
    }

    /**
     * @throws NoSuchEntityException
     */
    private function setVariablesToSender(SenderInterface $sender): void
    {
        if ($this->isAbandonedCartType()) {
            $this->setAbandonedCartVariablesToSender($sender);
        }
    }

    private function isAbandonedCartType(): bool
    {
        return in_array(
            $this->followUpEmail->getType(),
            [
                FollowUpEmailInterface::TYPE_ABANDONED_FIRST,
                FollowUpEmailInterface::TYPE_ABANDONED_SECOND,
                FollowUpEmailInterface::TYPE_ABANDONED_THIRD
            ]
        );
    }

    /**
     * @throws NoSuchEntityException
     */
    private function setAbandonedCartVariablesToSender(SenderInterface $sender): void
    {
        $sender->setQuote($this->cartRepository->get($this->followUpEmail->getConnectedEntityId()));
        $sender->setItemHtmlTemplate(
            $this->templateRepository->getById($this->followUpEmail->getTemplateId())
                ->getData('freshmail_additional_text')
        );
    }
}
