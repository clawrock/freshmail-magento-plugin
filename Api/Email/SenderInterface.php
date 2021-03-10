<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api\Email;

interface SenderInterface
{
    public function send(): void;
}
