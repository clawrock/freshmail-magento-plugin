<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData\Subscriber;

use Virtua\FreshMail\Api\RequestDataInterface;

interface GetMultipleInterface extends RequestDataInterface
{
    public function addSubscriber(string $email): void;

    public function getSubscribers(): array;

    public function setSubscribers(array $data): void;

    public function setList(string $list): void;

    public function getList(): string;

    public function getCount(): int;
}