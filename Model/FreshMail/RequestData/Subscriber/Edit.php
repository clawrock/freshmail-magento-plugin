<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Subscriber;

use Assert\Assert;
use Virtua\FreshMail\Model\FreshMail\StatusService;
use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;
use Virtua\FreshMail\Api\RequestData\Subscriber\EditInterface;

class Edit extends AbstractRequestData implements EditInterface
{
    public function __construct(
        string $email,
        string $list,
        ?int $state = null
    ) {
        $this->setEmail($email);
        $this->setList($list);
        $this->setState($state);
    }

    public function setEmail(string $email): void
    {
        $message = __('Email should be valid string (%email)', ['email' => $email]);
        Assert::that($email, $message)->email();
        $this->data['email'] = $email;
    }

    public function setList(string $list): void
    {
        $message = __('List hash cannot be a blank string');
        Assert::that($list, $message)->notBlank()->string();
        $this->data['list'] = $list;
    }

    public function setState(?int $state = null): void
    {
        if (null !== $state) {
            $message = __('Invalid value (%state) in state param', ['state' => $state]);
            Assert::that($state, $message)->choice(StatusService::allFreshMailSubscriberStatuses());
            $this->data['state'] = (string) $state;
        }
    }
}
