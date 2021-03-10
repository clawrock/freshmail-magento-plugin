<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData\Templates;

use Virtua\FreshMail\Api\RequestDataInterface;

interface TemplateInterface extends RequestDataInterface
{
    public function setHash(string $hash): void;
}
