<?php

namespace App\Tests\Functional\TestingTools;

use App\Exception\Security\InvalidCaptchaException;
use App\Google\CaptchaChecker;

class FakeCaptchaChecker extends CaptchaChecker
{
    private static bool $throwError = false;
    private static bool $hasBeenCalled = false;

    public function __construct(string $env)
    {
        // Disable parent constructor and add safety
        if ('test' !== $env) {
            throw new \LogicException('The class ' . static::class . ' is to be used in test env only!');
        }
    }

    public function setThrowInvalidCaptchaError($throwError = true): void
    {
        static::$throwError = $throwError;
    }

    public function hasBeenCalled(): bool
    {
        $result = static::$hasBeenCalled;
        static::$hasBeenCalled = false; // Auto reset

        return $result;
    }

    public function handleCaptcha(string $captchaUserResponse, ?string $userIp): void
    {
        static::$hasBeenCalled = true;

        if (true === static::$throwError) {
            static::$throwError = false; // Auto reset
            throw new InvalidCaptchaException(InvalidCaptchaException::ERROR_MSG);
        }
    }
}
