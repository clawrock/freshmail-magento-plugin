<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Flag\FlagResource;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;
use Virtua\FreshMail\Api\IntegrationServiceInterface;
use Virtua\FreshMail\Api\RequestData\IntegrationsInterfaceFactory;
use Virtua\FreshMail\Exception\ApiException;
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
     * @var Logger
     */
    protected $logger;

    /**
     * @throws LocalizedException
     */
    public function __construct(
        FreshMailApiInterfaceFactory $freshMailApiFactory,
        IntegrationActivationFlag $integrationActivationFlag,
        FlagResource $flagResource,
        IntegrationsInterfaceFactory $integrationRequestDataFactory,
        ProductMetadataInterface $productMetadata,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    ) {
        $this->freshMailApiFactory = $freshMailApiFactory;
        $this->integrationRequestDataFactory = $integrationRequestDataFactory;
        $this->integrationActivationFlag = $integrationActivationFlag->loadSelf();
        $this->flagResource = $flagResource;
        $this->productMetadata = $productMetadata;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function initIntegration(): void
    {
        $version = $this->getMagentoVersion();
        $url = $this->getBaseStoreUrl();
        $ip = $this->getShopIp();

        $data = [
            'version' => $version,
            'url' => $url,
            'ip' => $ip,
        ];

        $integrationRequest = $this->integrationRequestDataFactory->create($data);
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

    /**
     * @throws LocalizedException
     * @throws Exception
     */
    private function getShopIp(): string
    {
        $baseStoreUrl = $this->getBaseStoreUrl();
        $domain = parse_url($baseStoreUrl, PHP_URL_HOST);
        $ip = gethostbyname($domain);

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            $message = (string) __("Integration Activation failed. Failed getting IP address from hostname");
            $this->logger->logIfDebugModeOn($message);
            throw new Exception($message);
        } elseif (filter_var($ip, FILTER_FLAG_NO_PRIV_RANGE)) {
            $message = (string) __("Integration Activation failed. The IP address must not be withing a private range");
            $this->logger->logIfDebugModeOn($message);
            throw new Exception($message);
        }

        return $ip;
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
