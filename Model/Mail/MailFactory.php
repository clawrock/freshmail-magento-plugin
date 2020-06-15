<?php
// TODO - check if it is needed
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Mail;

use FreshMail\Api\Client\Service\Messaging\Mail;
use FreshMail\Api\Client\Service\Messaging\MailFactory as MessagingMailFactory;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Exception\ApiException;

class MailFactory
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MessagingMailFactory
     */
    protected $mailFactory;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        Config $config,
        MessagingMailFactory $mailFactory,
        Logger $logger
    ) {
        $this->config = $config;
        $this->mailFactory = $mailFactory;
        $this->logger = $logger;
    }

    /**
     * @throws ApiException
     */
    public function create(array $data = []): Mail
    {
        $data['bearerToken'] = $data['bearerToken'] ?? $this->config->getBearerToken();
        if (empty($data['bearerToken'])) {
            throw new ApiException(
                (string) __('The FreshMail API failed because of missing bearer token.')
            );
        }

        $mail = $this->mailFactory->create($data);
        $mail->setLogger($this->logger);

        return $mail;
    }
}
