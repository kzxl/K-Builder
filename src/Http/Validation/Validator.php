<?php

declare(strict_types=1);

namespace KBuilder\Http\Validation;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

/**
 * Wrapper mỏng quanh respect/validation để validate request body theo
 * một tập rule, gom lỗi theo field và ném ValidationException (422).
 *
 * Cách dùng trong controller:
 *
 *   $data = Validator::validate($body, [
 *       'title' => v::notEmpty()->stringType()->length(1, 255),
 *       'email' => v::notEmpty()->email(),
 *       'status'=> v::optional(v::in(['draft', 'published'])),
 *   ]);
 */
class Validator
{
    /**
     * @param array<string,mixed>            $data  Dữ liệu đầu vào (parsed body).
     * @param array<string,v>                $rules Map field => Validator rule.
     * @return array<string,mixed>                  Dữ liệu đã được lọc theo rule keys.
     *
     * @throws ValidationException khi có field không hợp lệ.
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            try {
                $rule->assert($value);
            } catch (NestedValidationException $e) {
                // Lấy thông điệp đầu tiên cho field này
                $messages = $e->getMessages();
                $errors[$field] = is_array($messages)
                    ? (string) reset($messages)
                    : (string) $messages;
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // Chỉ trả về các key được khai báo trong rules
        return array_intersect_key($data, $rules);
    }
}
