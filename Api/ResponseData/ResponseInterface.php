<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\ResponseData;

interface ResponseInterface
{
    public const NO_STATUS = 'NO STATUS';

    public function getData(): array;

    public function getDataErrors(): array;

    public function getErrors(): array;

    public function getStatus(): string;
}