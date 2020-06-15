<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Virtua\FreshMail\Api\FreshMailApiInterface;

interface FreshMailApiInterfaceFactory
{
    public function create(?string $bearerToken = ''): FreshMailApiInterface;
}