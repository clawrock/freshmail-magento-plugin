<?php
// TODO - check if it is needed
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Mail\MailBag;

use FreshMail\Api\Client\Messaging\Mail\ContentType;
use FreshMail\Api\Client\Messaging\Mail\Exception\ContentMismatchException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidContentBodyException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidCustomFieldException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidHeaderException;
use FreshMail\Api\Client\Messaging\Mail\MailBag;
use FreshMail\Api\Client\Messaging\Mail\MailBagFactory;
use Magento\Framework\Mail\EmailMessageInterface;
use Virtua\FreshMail\Logger\Logger;

class EmailMessage
{
    /**
     * @var MailBagFactory
     */
    protected $mailBagFactory;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        MailBagFactory $mailBagFactory,
        Logger $logger
    ) {
        $this->mailBagFactory = $mailBagFactory;
        $this->logger = $logger;
    }

    /**
     * @throws ContentMismatchException
     * @throws InvalidContentBodyException
     * @throws InvalidCustomFieldException
     * @throws InvalidHeaderException
     */
    public function getMailBag(EmailMessageInterface $message): MailBag
    {
        $mailBag = $this->mailBagFactory->create();
        $subject = $message->getSubject();
        $mailBag->setSubject($subject);

        $fromAddresses = $message->getFrom();

        foreach ($fromAddresses as $fromAddress) {
            $fromEmail = $fromAddress->getEmail();
            $fromName = $fromAddress->getName();
            $mailBag->setFrom($fromEmail, $fromName);
        }

        $recipientsTo = $message->getTo();
        foreach ($recipientsTo as $recipient) {
            $email = $recipient->getEmail();
            $mailBag->addRecipientTo($email);
        }

        //$body = $message->getMessageBody();
        $body = $message->getBody();
        $parts = $body->getParts();
        $rawEmailMessage = '';

        foreach ($parts as $part) {
            $rawEmailMessage = $part->getRawContent();
            break;
        }

        $contentType = $this->getContentType($message);
        if (ContentType::HTML === $contentType) {
            $mailBag->setHtml($rawEmailMessage);
        } elseif (ContentType::TEXT === $contentType) {
            $mailBag->setText($rawEmailMessage);
        } else {
            throw new \Exception('Unknown mail content type');
        }

        return $mailBag;
    }

    protected function getContentType(EmailMessageInterface $message): string
    {
        $contentTypeHeaderValue = $message->getHeaders()['Content-Type'];

        return explode(';', $contentTypeHeaderValue)[0];
    }
}
