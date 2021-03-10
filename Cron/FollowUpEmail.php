<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Cron;

use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterfaceFactory;
use Virtua\FreshMail\Api\FollowUpEmailRepositoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Virtua\FreshMail\Api\FollowUpEmailServiceInterface;

class FollowUpEmail
{
    private const CONFIG_SENDER_EMAIL = 'trans_email/ident_general/email';
    private const EMAIL_SENDER = 'general';

    /**
     * @var FollowUpEmailRepositoryInterface
     */
    private $followUpEmailRepository;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var CustomerViewHelper
     */
    private $customerViewHelper;

    /**
     * @var FollowUpEmailServiceInterface
     */
    private $followUpEmailService;

    public function __construct(
        FollowUpEmailRepositoryInterface $followUpEmailRepository,
        SenderResolverInterface $senderResolver,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        CustomerViewHelper $customerViewHelper,
        FollowUpEmailServiceInterface $followUpEmailService
    ) {
        $this->followUpEmailRepository = $followUpEmailRepository;
        $this->senderResolver = $senderResolver;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->customerViewHelper = $customerViewHelper;
        $this->followUpEmailService = $followUpEmailService;
    }

    public function execute(): void
    {
        $emailsToSend = $this->followUpEmailRepository->getScheduledEmails();
        foreach ($emailsToSend as $email) {
            try{
                $this->processAndSendEmail($email);
                $email->setSent(true);
                $this->followUpEmailRepository->save($email);
            } catch (\Exception $e) {
                $a = $e->getMessage();
                //todo process error
            }
        }
    }

    private function processAndSendEmail(FollowUpEmailInterface $email): void
    {
        $emailSender = $this->followUpEmailService->getEmailSenderForFollowUpEmail($email);
        $emailSender->send();
    }
}
