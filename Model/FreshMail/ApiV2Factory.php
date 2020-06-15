<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail;

use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;
use Magento\Framework\ObjectManagerInterface;

class ApiV2Factory
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

    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        Logger $logger
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function create(?string $bearerToken = ''): ApiV2
    {
        return $this->objectManager->create(ApiV2::class, [
            'logger' => $this->logger,
            'bearerToken' => $bearerToken ?: $this->config->getBearerToken()
        ]);
    }
}