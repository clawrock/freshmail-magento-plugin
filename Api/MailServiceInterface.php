<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use FreshMail\Api\Client\Exception\ClientException;
use FreshMail\Api\Client\Exception\RequestException;
use FreshMail\Api\Client\Exception\ServerException;
use FreshMail\Api\Client\Messaging\Mail\Exception\ContentMismatchException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidContentBodyException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidCustomFieldException;
use FreshMail\Api\Client\Messaging\Mail\Exception\InvalidHeaderException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\MessageInterface;
use Virtua\FreshMail\Exception\ApiException;

interface MailServiceInterface
{
    /**
     * @param EmailMessageInterface|MessageInterface $message
     *
     * @throws ApiException
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     * @throws ContentMismatchException
     * @throws InvalidContentBodyException
     * @throws InvalidCustomFieldException
     * @throws InvalidHeaderException
     */
    public function sendMessage($message): void;
}
