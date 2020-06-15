<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api\Data;

interface RequestQueueInterface
{
    public const ID = 'entity_id';
    public const NAME = 'name';
    public const ACTION = 'action';
    public const PARAMS = 'params';
    public const CREATED_AT = 'created_at';
    public const PROCESSED_AT = 'processed_at';
    public const STATUS = 'status';
    public const ERRORS = 'errors';

    public const CACHE_TAG = 'request_queue';

    public const ACTION_ADD_USER = 1;
    public const ACTION_EDIT_USER = 2;
    public const ACTION_RESIGN_USER = 3;
    public const ACTION_DELETE_USER = 4;
    public const ACTION_FULL_SYNC_EMAILS = 5;

    public const STATUS_PENDING = 1;
    public const STATUS_SUCCESS = 2;
    public const STATUS_ERROR = 3;

    public function getId(): ?int;

    public function setId($id);

    public function getAction(): int;

    public function setActon(int $value): void;

    public function getParamsArray(): array;

    public function setParamsArray(array $params): void;

    public function getParams(): string;

    public function setParams(string $params): void;

    public function getCreatedAt(): string;

    public function setCreatedAt(string $createdAt): void;

    public function getProcessedAt(): ?string;

    public function setProcessedAt(string $processedAt): void;

    public function getStatus(): int;

    public function setStatus(int $status): void;

    public function getErrors(): string;

    public function setErrors(string $errors): void;
}
