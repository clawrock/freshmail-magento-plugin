<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

interface SubscriberServiceInterface
{
    public function getSubscriberListHashByEmail(string $email): ?string;
}
