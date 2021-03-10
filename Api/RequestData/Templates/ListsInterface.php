<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api\RequestData\Templates;

use Virtua\FreshMail\Api\RequestDataInterface;

interface ListsInterface extends RequestDataInterface
{
    public function setLimit(int $limit): void;

    public function setOffset(int $offset): void;

    public function setDirectoryName(string $directoryName): void;
}
