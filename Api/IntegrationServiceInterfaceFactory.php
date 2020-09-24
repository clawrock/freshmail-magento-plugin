<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Virtua\FreshMail\Api\IntegrationServiceInterface;
use Virtua\FreshMail\Api\FreshMailApiInterface;

interface IntegrationServiceInterfaceFactory
{
    public function create(?FreshMailApiInterface $freshMailApiInterface = null): IntegrationServiceInterface;
}