<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail;

use Virtua\FreshMail\Api\ResponseData\ResponseInterface;

class Response implements ResponseInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var array
     */
    private $dataErrors;

    /**
     * @var string
     */
    private $status;

    public function __construct(array $response)
    {
        $this->errors = $response['errors'] ?? [];

        if (isset($response['data']) && isset($response['data']['errors'])) {
            $this->dataErrors = $response['data']['errors'];
            unset($response['data']['errors']);
        } else {
            $this->dataErrors = [];
        }

        $this->data = $response['data'] ?? [];
        $this->status = $response['status'] ?? self::NO_STATUS;


    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDataErrors(): array
    {
        return $this->dataErrors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}