<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Exception;

use Exception;

class ApiException extends Exception
{
    public static function createFromPreviousException(\Throwable $e, ?string $newMessage = null, ?int $customCode = null): self
    {
        if (null !== $newMessage) {
            $message = $newMessage;
        } else {
            $message = $e->getMessage();
        }

        if (null !== $customCode) {
            $code = $customCode;
        } else {
            $code = $e->getCode();
        }

        return new self($message, $code, $e);
    }
}
