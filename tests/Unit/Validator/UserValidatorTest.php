<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Validator;

use App\Validator\UserValidator;
use PHPUnit\Framework\TestCase;

class UserValidatorTest extends TestCase
{
    private UserValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UserValidator();
    }

    public function testValidateUsername(): void
    {
        $test = 'username_12';

        $this->assertSame($test, $this->validator->validateUsername($test));
    }

    public function testValidateUsernameEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The username can not be empty.');
        $this->validator->validateUsername(null);
    }

    public function testValidateUsernameInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The username must contain only lowercase latin characters and underscores.');
        $this->validator->validateUsername('INVALID');
    }

    public function testValidatePassword(): void
    {
        $test = 'password';

        $this->assertSame($test, $this->validator->validatePassword($test));
    }

    public function testValidatePasswordEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The password can not be empty.');
        $this->validator->validatePassword(null);
    }

    public function testValidatePasswordInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The password must be at least 6 characters long.');
        $this->validator->validatePassword('12345');
    }

    public function testValidateEmail(): void
    {
        $test = '@';

        $this->assertSame($test, $this->validator->validateEmail($test));
    }

    public function testValidateEmailEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The email can not be empty.');
        $this->validator->validateEmail(null);
    }

    public function testValidateEmailInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The email should look like a real email.');
        $this->validator->validateEmail('invalid');
    }

    public function testValidateFullName(): void
    {
        $test = 'Full Name';

        $this->assertSame($test, $this->validator->validateFullName($test));
    }

    public function testValidateFullNameEmpty()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The full name can not be empty.');
        $this->validator->validateFullName(null);
    }
}
