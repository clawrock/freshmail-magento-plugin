<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData;

use Virtua\FreshMail\Api\RequestData\IntegrationsInterface;

class Integrations implements IntegrationsInterface
{
    /**
     * @var string[]
     */
    protected $data;

    public function __construct(
        string $version,
        string $url,
        string $ip
    ) {
        $this->setMagentoVersion($version);
        $this->setUrl($url);
        $this->setIp($ip);
    }

    public function setMagentoVersion(string $version): void
    {
        $this->data['version'] = $version;
    }

    public function setUrl(string $url): void
    {
        $this->data['url'] = $url;
    }

    public function setIp(string $ip): void
    {
        $this->data['ip'] = $ip;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'plugin',
            'data' => [
                'vendor' => 'Magento',
                'version' => $this->data['version'],
                'url' => $this->data['url'],
                'ip' => $this->data['ip'],
            ],
        ];
    }
}
