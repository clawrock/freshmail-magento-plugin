<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData\Subscriber;

use Virtua\FreshMail\Api\RequestDataInterface;

interface AddMultipleInterface extends RequestDataInterface
{
    public function addSubscriber(string $email): void;

    public function setSubscribers(array $subscribers): void;

    public function setState(?int $state = null): void;

    public function getState(): int;

    public function setList(string $list): void;

    public function getList(): string;

    public function setConfirm(int $confirm = 0): void;

    public function getConfirm(): int;
}