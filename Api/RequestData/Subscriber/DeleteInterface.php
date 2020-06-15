<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData\Subscriber;

use Virtua\FreshMail\Api\RequestDataInterface;

interface DeleteInterface extends RequestDataInterface
{
    public function setEmail(string $email): void;

    public function getEmail(): string;

    public function setList(string $list): void;

    public function getList(): string;
}