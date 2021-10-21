<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class GetScopeContext
{
    /** @var RequestInterface */
    private $request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function execute(): array
    {
        $scopeData = $this->getFromCustomParams();
        if ($scopeData) {
            return $scopeData;
        }
        $store = $this->request->getParam('store');
        $website = $this->request->getParam('website');
        if ($store) {
            return [
                'scope' => ScopeInterface::SCOPE_STORE,
                'code' => $store
            ];
        }
        if ($website) {
            return [
                'scope' => ScopeInterface::SCOPE_WEBSITE,
                'code' => $website
            ];
        }

        return [
            'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            'code' => null
        ];
    }

    private function getFromCustomParams(): ?array
    {
        $scope = $this->request->getParam('scope');
        $scopeCode = (int) $this->request->getParam('scope_code');
        if (!$scope || !in_array($scope, [ScopeInterface::SCOPE_WEBSITE, ScopeInterface::SCOPE_STORE]) || !$scopeCode) {
            return null;
        }

        return [
            'scope' => $scope,
            'code' => $scopeCode
        ];
    }
}
