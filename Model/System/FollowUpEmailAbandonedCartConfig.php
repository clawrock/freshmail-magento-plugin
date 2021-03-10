<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class FollowUpEmailAbandonedCartConfig
{
    private const CONFIG_FUE_ABANDONED_CART_FIRST_EMAIL_ENABLED = 'follow_up_email/abandoned_cart/first_email_enabled';
    private const CONFIG_FUE_ABANDONED_CART_SECOND_EMAIL_ENABLED = 'follow_up_email/abandoned_cart/second_email_enabled';
    private const CONFIG_FUE_ABANDONED_CART_THIRD_EMAIL_ENABLED = 'aollow_up_email/abandoned_cart/third_email_enabled';

    private const CONFIG_FUE_ABANDONED_CART_FIRST_EMAIL_SEND_AFTER = 'follow_up_email/abandoned_cart/first_email_send_after';
    private const CONFIG_FUE_ABANDONED_CART_SECOND_EMAIL_SEND_AFTER = 'follow_up_email/abandoned_cart/second_email_send_after';
    private const CONFIG_FUE_ABANDONED_CART_THIRD_EMAIL_SEND_AFTER = 'follow_up_email/abandoned_cart/third_email_send_after';

    private const CONFIG_FUE_ABANDONED_CART_FIRST_EMAIL_TEMPLATE = 'follow_up_email/abandoned_cart/first_email_template';
    private const CONFIG_FUE_ABANDONED_CART_SECOND_EMAIL_TEMPLATE = 'follow_up_email/abandoned_cart/second_email_template';
    private const CONFIG_FUE_ABANDONED_CART_THIRD_EMAIL_TEMPLATE = 'follow_up_email/abandoned_cart/third_email_template';

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

    private function getStoreConfig(string $path, ?int $store = null): ?string
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getIsFirstEmailEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_FIRST_EMAIL_ENABLED, $storeId);
    }

    public function getIsSecondEmailEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_SECOND_EMAIL_ENABLED, $storeId);
    }

    public function getIsThirdEmailEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_THIRD_EMAIL_ENABLED, $storeId);
    }

    public function getFirstEmailSendAfter(?int $storeId = null): int
    {
        return (int) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_FIRST_EMAIL_SEND_AFTER, $storeId);
    }

    public function getSecondEmailSendAfter(?int $storeId = null): int
    {
        return (int) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_SECOND_EMAIL_SEND_AFTER, $storeId);
    }

    public function getThirdEmailSendAfter(?int $storeId = null): int
    {
        return (int) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_THIRD_EMAIL_SEND_AFTER, $storeId);
    }

    public function getFirstEmailTemplate(?int $storeId = null): int
    {
        return (int) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_FIRST_EMAIL_TEMPLATE, $storeId);
    }

    public function getSecondEmailTemplate(?int $storeId = null): int
    {
        return (int) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_SECOND_EMAIL_TEMPLATE, $storeId);
    }

    public function getThirdEmailTemplate(?int $storeId = null): int
    {
        return (int) $this->getStoreConfig(self::CONFIG_FUE_ABANDONED_CART_THIRD_EMAIL_TEMPLATE, $storeId);
    }
}
