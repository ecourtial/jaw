<?php

namespace App\Tests\Unit\Google;

use App\Exception\Security\InvalidCaptchaException;
use App\Google\CaptchaChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CaptchaCheckerTest extends TestCase
{
    private string $captchaKey = 'toto';
    private HttpClientInterface $client;
    private CaptchaChecker $checker;

    public function setUp(): void
    {
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->checker = new CaptchaChecker($this->client, $this->captchaKey);
    }

    public function testValidCaptchaWithoutIp(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(static::once())->method('getStatusCode')->willReturn(200);
        $response->expects(static::once())->method('getContent')->willReturn('{"success":true}');

        // Test without IP
        $this->client->expects(static::once())->method('request')
            ->with(
                'POST',
                'https://www.google.com/recaptcha/api/siteverify',
                ['body' => [
                    'secret' => $this->captchaKey,
                    'response' => 'someUserResponse',
                ]],
            )
            ->willReturn($response);

        $this->checker->handleCaptcha('someUserResponse', null);
    }

    public function testValidCaptchaWithIp(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(static::once())->method('getStatusCode')->willReturn(200);
        $response->expects(static::once())->method('getContent')->willReturn('{"success":true}');

        // Test with IP
        $this->client->expects(static::once())->method('request')
            ->with(
                'POST',
                'https://www.google.com/recaptcha/api/siteverify',
                ['body' => [
                    'secret' => $this->captchaKey,
                    'response' => 'someUserResponse',
                    'remoteip' => '1.2.3.4'
                ]],
            )
            ->willReturn($response);

        $this->checker->handleCaptcha('someUserResponse', '1.2.3.4');
    }

    public function testInvalidStatusCode(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(static::once())->method('getStatusCode')->willReturn(666);

        $this->client->expects(static::once())->method('request')->willReturn($response);

        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Error when contacting Google for Captcha validation. HTTP Status code was: 666.');

        $this->checker->handleCaptcha('someUserResponse', '1.2.3.4');
    }

    public function testInvalidResponseBody(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(static::once())->method('getStatusCode')->willReturn(200);
        $response->expects(static::once())->method('getContent')->willReturn('{"foofoo":true}');

        $this->client->expects(static::once())->method('request')->willReturn($response);

        static::expectException(\LogicException::class);
        static::expectExceptionMessage("Invalid Google response. The 'success' key is missing!");

        $this->checker->handleCaptcha('someUserResponse', '1.2.3.4');
    }

    public function testInvalidCaptcha(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(static::once())->method('getStatusCode')->willReturn(200);
        $response->expects(static::once())->method('getContent')->willReturn('{"success":false}');

        $this->client->expects(static::once())->method('request')->willReturn($response);

        static::expectException(InvalidCaptchaException::class);
        static::expectExceptionMessage("Invalid captcha response!");

        $this->checker->handleCaptcha('someUserResponse', '1.2.3.4');
    }
}
