<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail;

use Magento\Framework\ObjectManagerInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;

class ApiV3Factory
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

    public function create(?string $bearerToken = ''): ApiV3
    {
        return $this->objectManager->create(APiV3::class, [
            'logger' => $this->logger,
            'bearerToken' => $bearerToken ?: $this->config->getBearerToken()
        ]);
    }
}
