<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const CONFIG_FRESHMAIL_CONNECTION_API_BEARER_TOKEN = 'freshmail/connection/bearer_token';
    private const CONFIG_FRESHMAIL_HASH_LIST = 'freshmail/lists/list';
    private const CONFIG_FRESHMAIL_ENABLED = 'freshmail/connection/enabled';
    private const CONFIG_FRESHMAIL_TRANSACTIONAL_EMAILS_ENABLE_API_SEND = 'freshmail/transactional_emails/enable_api_send';
    private const CONFIG_FRESHMAIL_DEBUG_MODE = 'freshmail/debug/debug';

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
    }

    protected function getStoreConfig(string $path, ?int $store = null): ?string
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getBearerToken(): string
    {
        $bearerToken = $this->getStoreConfig(self::CONFIG_FRESHMAIL_CONNECTION_API_BEARER_TOKEN);

        return $bearerToken ? $this->encryptor->decrypt($bearerToken) : '';
    }

    public function getListHashByStoreId(?int $storeId = null): string
    {
        $listHash = $this->getStoreConfig(self::CONFIG_FRESHMAIL_HASH_LIST, $storeId);

        return $listHash ?: '';
    }

    public function isEnabled(?int $store = null): bool
    {
        return (bool) $this->getStoreConfig(self::CONFIG_FRESHMAIL_ENABLED, $store);
    }

    public function getTransactionalEmailsSendByAPI(?int $store = null): bool
    {
        return (bool) $this->getStoreConfig(self::CONFIG_FRESHMAIL_TRANSACTIONAL_EMAILS_ENABLE_API_SEND, $store);
    }

    public function isDebugMode(): bool
    {
        return (bool) $this->getStoreConfig(self::CONFIG_FRESHMAIL_DEBUG_MODE);
    }
}
