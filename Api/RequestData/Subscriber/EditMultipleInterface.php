<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData\Subscriber;

use Virtua\FreshMail\Api\RequestDataInterface;

interface EditMultipleInterface extends RequestDataInterface
{
    public function addSubscriber(string $email): void;

    public function getSubscribers(): array;

    public function setSubscribers(array $data): void;

    public function setState(?int $state = null): void;

    public function getState(): ?int;

    public function setList(string $list): void;

    public function getList(): string;
}