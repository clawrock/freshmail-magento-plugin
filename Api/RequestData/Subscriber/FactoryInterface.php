<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData\Subscriber;

use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;

interface FactoryInterface
{
    public function create(array $data = []): AbstractRequestData;

    public function createFromJson(string $jsonData): AbstractRequestData;
}