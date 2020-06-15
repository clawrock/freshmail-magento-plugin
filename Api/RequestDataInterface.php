<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

interface RequestDataInterface
{
    public function getDataArray(): array;
}