<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData\Subscriber;

use Virtua\FreshMail\Api\RequestDataInterface;

interface AddInterface extends RequestDataInterface
{
    public function setEmail(string $email): void;

    public function getEmail(): string;

    public function setList(string $list): void;

    public function getList(): string;

    public function setState(?int $state = null): void;

    public function getState(): ?int;

    public function setConfirm(int $confirm = 0): void;

    public function getConfirm(): int;
}