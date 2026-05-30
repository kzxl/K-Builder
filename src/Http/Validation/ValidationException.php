<?php

declare(strict_types=1);

namespace KBuilder\Http\Validation;

use RuntimeException;

/**
 * Ném ra khi dữ liệu đầu vào không hợp lệ. Chứa map lỗi theo field.
 */
class ValidationException extends RuntimeException
{
    /** @param array<string,string> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Dữ liệu không hợp lệ', 422);
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
