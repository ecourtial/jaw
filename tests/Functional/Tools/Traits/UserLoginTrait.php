<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\Tools\Traits;

use App\Google\CaptchaChecker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait UserLoginTrait
{
    private static $adminUrl = 'http://localhost/admin';

    private function loginWithSuccess(KernelBrowser $client, string $username, string $password): void
    {
        $client->request('GET', self::$adminUrl);
        static::assertEquals(302, $client->getResponse()->getStatusCode());
        static::assertEquals('http://localhost/login', $client->getResponse()->headers->get('Location'));

        $client->followRedirects();
        $crawler = $client->request('GET', self::$adminUrl);
        $this->assertPageTitleContains('MyBlog Admin - Connection - JAW v1.0');

        $form = $crawler->selectButton('loginSubmitButton')->form([
            '_username' => $username,
            '_password' => $password,
        ]);

        // to help: var_dump($values = $form->getPhpValues()); exit;

        $client->submit($form);

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertPageTitleContains('MyBlog Admin - Home - JAW v1.0');
        static::assertTrue($client->getContainer()->get(CaptchaChecker::class)->hasBeenCalled());

        $client->followRedirects(false);
    }

    public function loginFailureBecauseOfBadCredentials(KernelBrowser $client): void
    {
        $client->request('GET', self::$adminUrl);
        static::assertEquals(302, $client->getResponse()->getStatusCode());
        static::assertEquals('http://localhost/login', $client->getResponse()->headers->get('Location'));

        $client->followRedirects();
        $crawler = $client->request('GET', self::$adminUrl);
        $this->assertPageTitleContains('MyBlog Admin - Connection - JAW v1.0');

        $form = $crawler->selectButton('loginSubmitButton')->form([
            '_username' => 'wow',
            '_password' => 'great',
        ]);

        $client->submit($form);

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertPageTitleContains('MyBlog Admin - Connection - JAW v1.0');

        $client->followRedirects(false);
    }

    public function loginFailureBecauseOfBadCaptchaAnswer(KernelBrowser $client, string $username, string $password): void
    {
        $client->request('GET', self::$adminUrl);
        static::assertEquals(302, $client->getResponse()->getStatusCode());
        static::assertEquals('http://localhost/login', $client->getResponse()->headers->get('Location'));

        $client->followRedirects();
        $crawler = $client->request('GET', self::$adminUrl);
        $this->assertPageTitleContains('MyBlog Admin - Connection - JAW v1.0');

        $form = $crawler->selectButton('loginSubmitButton')->form([
            '_username' => $username,
            '_password' => $password,
        ]);

        // to help: var_dump($values = $form->getPhpValues()); exit;

        $client->getContainer()->get(CaptchaChecker::class)->setThrowInvalidCaptchaError();

        $client->submit($form);

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertPageTitleContains('MyBlog Admin - Connection - JAW v1.0');
        static::assertTrue($client->getContainer()->get(CaptchaChecker::class)->hasBeenCalled());

        $client->followRedirects(false);
    }
}
