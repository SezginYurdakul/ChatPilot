<?php

namespace App\Support;

class ApiErrorCatalog
{
    public const UNAUTHENTICATED = 'CP-AUTH-001';
    public const FORBIDDEN = 'CP-AUTH-002';
    public const VALIDATION_FAILED = 'CP-REQ-001';
    public const NOT_FOUND = 'CP-REQ-404';
    public const TOO_MANY_REQUESTS = 'CP-RATE-001';
    public const CONFLICT = 'CP-DOM-001';
    public const SERVER_ERROR = 'CP-SYS-001';
    public const HTTP_ERROR = 'CP-HTTP-001';

    public static function fromErrorKey(string $error): string
    {
        return match ($error) {
            'unauthenticated' => self::UNAUTHENTICATED,
            'forbidden' => self::FORBIDDEN,
            'validation_failed' => self::VALIDATION_FAILED,
            'not_found' => self::NOT_FOUND,
            'too_many_requests' => self::TOO_MANY_REQUESTS,
            'conflict' => self::CONFLICT,
            'server_error' => self::SERVER_ERROR,
            default => self::HTTP_ERROR,
        };
    }
}
