<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetBearerTokenForListHash
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var GetBearerTokenForStore */
    private $getBearerTokenForStore;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        GetBearerTokenForStore $getBearerTokenForStore
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->getBearerTokenForStore = $getBearerTokenForStore;
    }

    public function execute(string $listHash): string
    {
        if (!$listHash) {
            return '';
        }
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $hash = $this->scopeConfig->getValue(
                'freshmail/lists/list',
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
            if ($listHash === $hash) {
                return $this->getBearerTokenForStore->execute((int) $store->getId());
            }
        }

        return '';
    }
}
