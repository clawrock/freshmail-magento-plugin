<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api\Email;

use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;

interface SenderFactoryInterface
{
    public function create(FollowUpEmailInterface $followUpEmail): SenderInterface;
}
