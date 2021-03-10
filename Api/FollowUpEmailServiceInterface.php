<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Virtua\FreshMail\Api\Email\SenderInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;

interface FollowUpEmailServiceInterface
{
    public function getEmailSenderForFollowUpEmail(FollowUpEmailInterface $email): SenderInterface;
}
