<?php

declare(strict_types=1);

namespace KBuilder\Tests\Unit;

use KBuilder\Http\Validation\Validator;
use KBuilder\Http\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as v;

class ValidatorTest extends TestCase
{
    public function testPassesValidData(): void
    {
        $data = ['title' => 'Hello', 'slug' => 'hello-world', 'extra' => 'ignored'];
        $result = Validator::validate($data, [
            'title' => v::notEmpty()->stringType()->length(1, 255),
            'slug'  => v::notEmpty()->slug(),
        ]);

        // Chỉ trả về các key được khai báo trong rules
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('slug', $result);
        $this->assertArrayNotHasKey('extra', $result);
    }

    public function testThrowsOnEmptyRequired(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validate(['title' => ''], [
            'title' => v::notEmpty()->stringType(),
        ]);
    }

    public function testExceptionCarriesFieldErrors(): void
    {
        try {
            Validator::validate(['email' => 'not-an-email'], [
                'email' => v::notEmpty()->email(),
            ]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(422, $e->getCode());
            $errors = $e->errors();
            $this->assertArrayHasKey('email', $errors);
            $this->assertNotEmpty($errors['email']);
        }
    }

    public function testInvalidSlugRejected(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validate(['slug' => 'Not A Slug!!'], [
            'slug' => v::notEmpty()->slug(),
        ]);
    }

    public function testMultipleErrorsCollected(): void
    {
        try {
            Validator::validate(['title' => '', 'slug' => 'bad slug'], [
                'title' => v::notEmpty()->stringType(),
                'slug'  => v::notEmpty()->slug(),
            ]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('title', $errors);
            $this->assertArrayHasKey('slug', $errors);
        }
    }
}
