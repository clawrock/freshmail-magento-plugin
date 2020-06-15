<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Virtua\FreshMail\Api\Data\RequestQueueInterface;

interface RequestQueueProcessorInterface
{
    public function process(RequestQueueInterface $requestQueue): void;
}