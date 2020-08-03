<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Subscriber;

use Virtua\FreshMail\Api\RequestData\Subscriber\AddInterface;
use Assert\Assert;
use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;
use Virtua\FreshMail\Api\FreshMailStatusServiceInterface;
use Virtua\FreshMail\Model\FreshMail\StatusService;

class Add extends AbstractRequestData implements AddInterface
{
    public function __construct(
        string $email,
        string $list,
        ?int $state = null,
        int $confirm = 0
    ) {
        $this->setEmail($email);
        $this->setList($list);
        $this->setState($state);
        $this->setConfirm($confirm);
    }

    public static function setDataFromArray(array $data): self
    {
        $email = $data['email'] ?? '';
        $list = $data['list'] ?? '';
        $state = $data['state'] ?? null;
        $confirm = $data['confirm'] ?? 0;

        return new self($email, $list, $state, $confirm);
    }

    public function setEmail(string $email): void
    {
        $message = __('Email should be valid string (%email)', ['email' => $email]);
        Assert::that($email, $message)->email();
        $this->data['email'] = $email;
    }

    public function getEmail(): string
    {
        return $this->data['email'];
    }

    public function setList(string $list): void
    {
        $message = __('List hash cannot be a blank string');
        Assert::that($list, $message)->notBlank()->string();
        $this->data['list'] = $list;
    }

    public function getList(): string
    {
        return $this->data['list'];
    }

    public function setState(?int $state = null): void
    {
        if (null === $state) {
            $state = FreshMailStatusServiceInterface::SUBSCRIBER_STATUS_AWAITS_ACTIVATION;
        }

        $message = __('Invalid value (%state) in state param', ['state' => $state]);
        Assert::that($state, $message)->choice(StatusService::allFreshMailSubscriberStatuses());
        $this->data['state'] = $state;
    }

    public function getState(): int
    {
        return $this->data['state'];
    }

    public function setConfirm(int $confirm = 0): void
    {
        $message = __('Invalid value (%confirm) in confirm param', ['confirm' => $confirm]);
        Assert::that($confirm, $message)->choice([0, 1]);
        $this->data['confirm'] = $confirm;
    }

    public function getConfirm(): int
    {
        return $this->data['confirm'];
    }
}
