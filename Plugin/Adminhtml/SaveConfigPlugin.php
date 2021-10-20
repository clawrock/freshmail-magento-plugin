<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Plugin\Adminhtml;

use FreshMail\Api\Client\Exception\ClientException;
use FreshMail\Api\Client\Exception\RequestException;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Api\IntegrationServiceInterfaceFactory;
use Virtua\FreshMail\Api\IntegrationServiceInterface;
use Virtua\FreshMail\Model\System\Config as FreshMailConfig;
use Virtua\FreshMail\Exception\ApiException;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;

class SaveConfigPlugin
{
    private const SECTION_NAME = 'freshmail';

    /**
     * @var FreshMailConfig
     */
    private $config;

    /**
     * @var bool
     */
    private $beforeSaveModuleIsEnabled;

    /**
     * @var RequestQueueServiceInterface
     */
    private $requestQueueService;

    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;

    /**
     * @var IntegrationServiceInterfaceFactory
     */
    private $integrationServiceFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        FreshMailConfig $config,
        RequestQueueServiceInterface $requestQueueService,
        FreshMailApiInterfaceFactory $freshMailApiFactory,
        IntegrationServiceInterfaceFactory $integrationServiceFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->requestQueueService = $requestQueueService;
        $this->freshMailApiFactory = $freshMailApiFactory;
        $this->integrationServiceFactory = $integrationServiceFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @throws AlreadyExistsException
     * @throws ApiException
     * @throws ClientException
     * @throws LocalizedException
     * @throws RequestException
     */
    public function beforeSave(Config $subject): void
    {
        $sectionData = $subject->getData();
        if (!isset($sectionData['groups']['connection']['fields']['enabled']['value'])) {
            $enabled = $this->scopeConfig->isSetFlag('freshmail/connection/enabled');
            $sectionData['groups']['connection']['fields']['enabled']['value'] = $enabled;
            $subject->setData($sectionData);
        }
        $sectionId = $subject->getSection();
        if (self::SECTION_NAME === $sectionId) {
            $sectionData = $subject->getData();
            $value = $sectionData['groups']['connection']['fields']['bearer_token']['value'];

            if ($this->checkToReadFromConfig($value)) {
                $bearerToken = $this->config->getBearerToken();
            } else {
                $bearerToken = $value;
            }

            $freshMailApi = $this->freshMailApiFactory->create($bearerToken);
            if (! $freshMailApi->testConnection()) {
                throw new ApiException((string) __('Connection failed!'));
            }

            $integrationService = $this->getIntegrationService($freshMailApi);
            if ($integrationService->isIntegrationNeeded()) {
                $integrationService->initIntegration();
            }

            $this->beforeSaveModuleIsEnabled = $this->config->isEnabled();
        }
    }

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function afterSave(Config $subject, Config $result): void
    {
        $sectionId = $subject->getSection();
        if (self::SECTION_NAME === $sectionId) {
            $resultData = $result->getData();
            $afterSaveModuleIsEnabled = (bool) $resultData['groups']['connection']['fields']['enabled']['value'];

            if (! $this->beforeSaveModuleIsEnabled && $afterSaveModuleIsEnabled) {
                $this->requestQueueService->addFullSyncToQueue();
            }
        }
    }

    private function getIntegrationService(FreshMailApiInterface $freshMailApi): IntegrationServiceInterface
    {
        return $this->integrationServiceFactory->create($freshMailApi);
    }

    private function checkToReadFromConfig(string $string): bool //TODO move to a separate class
    {
        if (false !== mb_strpos($string, '*')) {
            return $this->allCharsAreTheSame($string);
        }

        return false;
    }

    private function allCharsAreTheSame(string $string): bool //TODO move to a separate class
    {
        $chars = str_split($string);
        $lastChar = $chars[0];
        foreach ($chars as $char) {
            $current = $char;
            if ($current !== $lastChar) {
                return false;
            }

            $lastChar = $char;
        }

        return true;
    }
}
