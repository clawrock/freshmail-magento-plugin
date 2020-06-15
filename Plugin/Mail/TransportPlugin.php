<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Plugin\Mail;

use Closure;
use FreshMail\Api\Client\Exception\ClientException;
use FreshMail\Api\Client\Exception\RequestException;
use FreshMail\Api\Client\Exception\ServerException;
use FreshMail\Api\Client\Messaging\Mail\Exception\ContentMismatchException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidContentBodyException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidCustomFieldException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidHeaderException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterface;
use ReflectionClass;
use ReflectionException;
use Virtua\FreshMail\Api\MailServiceInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config as FreshMailConfig;
use Virtua\FreshMail\Exception\ApiException;

class TransportPlugin
{
    /**
     * @var FreshMailConfig
     */
    protected $config;

    /**
     * @var MailServiceInterface
     */
    protected $mailServiceInterface;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        FreshMailConfig $config,
        MailServiceInterface $mailServiceInterface,
        Logger $logger
    ) {
        $this->config = $config;
        $this->mailServiceInterface = $mailServiceInterface;
        $this->logger = $logger;
    }

    public function aroundSendMessage(TransportInterface $subject, Closure $proceed): void
    {
        if ($this->allowedToSendEmails()) {
            try {
                $this->send($subject, null);
            } catch (\Throwable $e) {
                $this->logger->error($e);
                $proceed();
            }
        } else {
            $proceed();
        }
    }

    protected function allowedToSendEmails(): bool
    {
        return $this->config->isEnabled() && $this->config->getTransactionalEmailsSendByAPI();
    }

    /**
     * @throws ApiException
     * @throws ContentMismatchException
     * @throws InvalidContentBodyException
     * @throws InvalidCustomFieldException
     * @throws InvalidHeaderException
     * @throws ReflectionException
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    protected function send(TransportInterface $subject, ?int $storeId = null): void
    {
        $message = $this->getMessage($subject);
        $this->mailServiceInterface->sendMessage($message);
    }

    /**
     * @return MessageInterface|EmailMessageInterface
     *
     * @throws ReflectionException
     */
    protected function getMessage(TransportInterface $subject)
    {
        if (method_exists($subject, 'getMessage')) {
            $message = $subject->getMessage();
        } else {
            $message = $this->useReflectionToGetMessage($subject);
        }

        return $message;
    }

    /**
     * @return MessageInterface|EmailMessageInterface
     *
     * @throws ReflectionException
     */
    protected function useReflectionToGetMessage(TransportInterface $subject)
    {
        $reflection = new ReflectionClass($subject);
        $property = $reflection->getProperty('_message');
        $property->setAccessible(true);

        return $property->getValue($subject);
    }
}
