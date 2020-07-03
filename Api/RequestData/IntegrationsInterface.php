<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData;

interface IntegrationsInterface extends \JsonSerializable
{
    public function setMagentoVersion(string $version): void;

    public function setUrl(string $url): void;

    public function setIp(string $ip): void;
}
