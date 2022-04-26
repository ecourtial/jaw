<?php

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\UserLoginSubscriber;
use App\Google\CaptchaChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class UserLoginSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        static::assertEquals([CheckPassportEvent::class => 'handleCaptcha'], UserLoginSubscriber::getSubscribedEvents());
    }

    public function testHandleCaptchaDoesNotApply(): void
    {
        $captchaChecker = $this->createMock(CaptchaChecker::class);
        $requestStack = $this->createMock(RequestStack::class);
        $event = $this->createMock(CheckPassportEvent::class);
        $request = $this->createMock(Request::class);

        $requestStack->expects(static::once())->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::once())->method('getRequestUri')->willReturn('/toto');
        $captchaChecker->expects(static::never())->method('handleCaptcha');

        $subscriber = new UserLoginSubscriber($requestStack, $captchaChecker);
        $subscriber->handleCaptcha($event);
    }

    public function testHandleCaptchaDoesApply(): void
    {
        $captchaChecker = $this->createMock(CaptchaChecker::class);
        $requestStack = $this->createMock(RequestStack::class);
        $event = $this->createMock(CheckPassportEvent::class);
        $request = $this->createMock(Request::class);

        $requestStack->expects(static::once())->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::once())->method('get')->with('g-recaptcha-response')->willReturn('whatever');
        $request->expects(static::once())->method('getClientIp')->willReturn('1.2.4.4');
        $request->expects(static::once())->method('getRequestUri')->willReturn('/login');

        $captchaChecker->expects(static::once())->method('handleCaptcha')->with('whatever', '1.2.4.4');

        $subscriber = new UserLoginSubscriber($requestStack, $captchaChecker);
        $subscriber->handleCaptcha($event);
    }
}
