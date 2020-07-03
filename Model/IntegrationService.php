<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\HTTP\PhpEnvironment\ServerAddress;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;
use Virtua\FreshMail\Api\IntegrationServiceInterface;
use Virtua\FreshMail\Api\RequestData\IntegrationsInterfaceFactory;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\Flag\IntegrationActivationFlag;

class IntegrationService implements IntegrationServiceInterface
{
    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;

    /**
     * @var FreshMailApiInterface
     */
    private $freshMailApi;

    /**
     * @var IntegrationsInterfaceFactory
     */
    private $integrationRequestDataFactory;

    /**
     * @var FlagResource
     */
    private $flagResource;

    /**
     * @var IntegrationActivationFlag
     */
    private $integrationActivationFlag;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ServerAddress
     */
    private $serverAddress;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        FreshMailApiInterfaceFactory $freshMailApiFactory,
        IntegrationActivationFlag $integrationActivationFlag,
        FlagResource $flagResource,
        IntegrationsInterfaceFactory $integrationRequestDataFactory,
        ProductMetadataInterface $productMetadata,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ServerAddress $serverAddress,
        Logger $logger
    ) {
        $this->freshMailApiFactory = $freshMailApiFactory;
        $this->integrationRequestDataFactory = $integrationRequestDataFactory;
        $this->integrationActivationFlag = $integrationActivationFlag;
        $this->flagResource = $flagResource;
        $this->productMetadata = $productMetadata;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->serverAddress = $serverAddress;
        $this->logger = $logger;
    }

    public function initIntegration(): void
    {
        $version = $this->getMagentoVersion();
        $url = $this->getBaseStoreUrl();
        $ip = $this->getShopIp(); // $ip = '172.217.22.14' ip must be public

        $integrationRequest = $this->integrationRequestDataFactory->create([
            'version' => $version,
            'url' => $url,
            'ip' => $ip,
        ]);

        $this->getFreshMailApi()->integrations($integrationRequest);
        $this->saveIntegrationActivationFlag(1);
    }

    private function getFreshMailApi(): FreshMailApiInterface
    {
        if (! $this->freshMailApi) {
            $this->freshMailApi = $this->freshMailApiFactory->create();
        }

        return $this->freshMailApi;
    }

    private function getMagentoVersion(): string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @throws LocalizedException
     */
    private function getBaseStoreUrl(): string
    {
        $website = $this->storeManager->getWebsite($this->request->getParam('website'));

        $isSecure = $this->scopeConfig->isSetFlag(
            Store::XML_PATH_SECURE_IN_FRONTEND,
            ScopeInterface::SCOPE_WEBSITE,
            $website->getCode()
        );

        $configPath = $isSecure ? Store::XML_PATH_SECURE_BASE_LINK_URL : Store::XML_PATH_UNSECURE_BASE_LINK_URL;

        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_WEBSITE, $website->getCode());
    }

    private function getShopIp(): string
    {
        return $this->serverAddress->getServerAddress();
    }

    public function checkToActiveTheIntegration(): bool
    {
        if ($this->integrationActivationFlag->hasData('flag_data')) {
            $flagData = (bool) $this->integrationActivationFlag->getFlagData();
            return ! $flagData;
        }

        return true;
    }

    /**
     * @throws AlreadyExistsException
     */
    public function saveIntegrationActivationFlag(int $value): void
    {
        $this->integrationActivationFlag->setFlagData($value);
        $this->flagResource->save($this->integrationActivationFlag);
    }
}
