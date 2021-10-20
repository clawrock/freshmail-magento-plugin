<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class GetBearerTokenForStore
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;
    /** @var Encryptor */
    private $encryptor;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Encryptor $encryptor,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    public function execute(int $storeId): string
    {
        $token = (string) $this->scopeConfig->getValue(
            'freshmail/connection/bearer_token',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        try {
            return $this->encryptor->decrypt($token);
        } catch (\Throwable $t) {
            $this->logger->error(
                (string) __('FreshMail token decryption for store %1 failed!', $storeId),
                ['exception' => $t]
            );

            return '';
        }
    }
}
