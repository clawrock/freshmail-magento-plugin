<?php
// TODO - check if it is needed
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Mail;

use FreshMail\Api\Client\Exception\ClientException;
use FreshMail\Api\Client\Exception\RequestException;
use FreshMail\Api\Client\Exception\ServerException;
use FreshMail\Api\Client\Messaging\Mail\MailBag;
use Virtua\FreshMail\Api\MailServiceInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Exception\ApiException;
use Virtua\FreshMail\Model\Mail\MailBag\EmailMessage;

class MailService implements MailServiceInterface
{
    /**
     * @var MailFactory
     */
    protected $mailFactory;

    /**
     * @var EmailMessage
     */
    protected $emailMessage;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        MailFactory $mailFactory,
        EmailMessage $emailMessage,
        Logger $logger
    ) {
        $this->mailFactory = $mailFactory;
        $this->emailMessage = $emailMessage;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage($message): void
    {
        $mailBag = $this->emailMessage->getMailBag($message);
        $this->sendEmail($mailBag);
    }

    /**
     * @throws ApiException
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    protected function sendEmail(MailBag $mailBag): void
    {
        $mail = $this->mailFactory->create();
        $mail->send($mailBag);
    }
}
