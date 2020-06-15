<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Status;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;

class ModuleDisableByCliPlugin
{
    private const FRESH_MAIL_MODULE = 'Virtua_FreshMail';

    /**
     * @var bool
     */
    private $toDisable = true;

    /**
     * @var Config
     */
    private $config;
    
    private $requestQueueService;

    public function __construct(
        Config $config,
        RequestQueueServiceInterface $requestQueueService
    ) {
        $this->config = $config;
        $this->requestQueueService = $requestQueueService;
    }

    public function beforeGetModulesToChange(Status $subject, bool $isEnabled, array $modules): void
    {
        if (! $this->config->isEnabled()) {
            return;
        }

        $this->toDisable = ! $isEnabled;
    }

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function afterGetModulesToChange(Status $subject, array $changed): array
    {
        if ($this->config->isEnabled()) {
            if ($this->toDisable && in_array(self::FRESH_MAIL_MODULE, $changed, true)) {
                $this->requestQueueService->addFullSyncToQueue();
            }
        }

        return $changed;
    }
}
