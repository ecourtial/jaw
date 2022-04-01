<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\Security\InvalidCaptchaException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class UserLoginSubscriber implements EventSubscriberInterface
{
    private Request $request;
    private HttpClientInterface $httpClient;
    private string $recaptchaPrivateKey;

    public function __construct(
        RequestStack $requestStack,
        HttpClientInterface $httpClient,
        string $recaptchaPrivateKey,
    ) {
        $this->request = $requestStack->getCurrentRequest() ?? throw new \RuntimeException('Main request cannot be null');
        $this->httpClient = $httpClient;
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;
    }

    public static function getSubscribedEvents()
    {
        return [CheckPassportEvent::class => 'handleCaptcha'];
    }

    public function handleCaptcha(): void
    {
        $googleResponse = $this->httpClient->request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'body' => [
                    'secret' => $this->recaptchaPrivateKey,
                    'response' => $this->request->get('g-recaptcha-response', ''),
                    'remoteip' => $this->request->getClientIp()
                ],
            ],
        );

        $statusCode = $googleResponse->getStatusCode();

        if ($statusCode !== 200) {
            throw new \RuntimeException(
                'Erreur when contacting Google for Captcha validation. HTTP Status code was: ' . $statusCode
            );
        }

        $response = \json_decode($googleResponse->getContent(), true);

        /** @var array{'success': bool} $response */
        if ($response['success'] === false) {
            throw new InvalidCaptchaException(InvalidCaptchaException::ERROR_MSG);
        }
    }
}
