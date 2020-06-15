<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use \Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Virtua\FreshMail\Api\ClientInterface;
use Virtua\FreshMail\Model\FreshMail\ApiV2Factory;
use Virtua\FreshMail\Model\FreshMail\ApiV2 as ApiV2Client;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Magento\Framework\ObjectManagerInterface;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;

class FreshMailApiFactory implements FreshMailApiInterfaceFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ApiV2Factory
     */
    private $apiV2Factory;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        Logger $logger,
        ApiV2Factory $apiV2Factory
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->logger = $logger;
        $this->apiV2Factory = $apiV2Factory;
    }

    public function create(?string $bearerToken = ''): FreshMailApiInterface
    {
        return $this->objectManager->create(FreshMailApiInterface::class, [
            'apiV2Factory' => $this->apiV2Factory,
            'config' => $this->config,
            'logger' => $this->logger,
            'bearerToken' => $bearerToken ?: $this->config->getBearerToken()
        ]);
    }
}