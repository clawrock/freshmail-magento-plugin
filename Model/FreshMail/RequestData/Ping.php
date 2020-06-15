<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData;

class Ping implements \JsonSerializable
{
    public function getData(): array
    {
        return [
            'response' => 'pong',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->getData();
    }
}
