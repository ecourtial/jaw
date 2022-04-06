<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\Tools\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait UserProfileTrait
{
    private static string $profileUrl = 'http://localhost/admin/profile';
    private static string $changePasswordUrl = 'http://localhost/admin/password';

    private function getToken(KernelBrowser $client): string
    {
        $crawler = $client->request('GET', self::$profileUrl);
        $divContent = $crawler->filter('#userToken')->text();

        return trim(explode(': ', $divContent)[1]);
    }

    private function gotoProfileAndCheckUserFullNameAndEmail(
        KernelBrowser $client,
        string $expectedUserFullName,
        string $expectedUserEmail
    ): void {
        $crawler = $client->request('GET', self::$profileUrl);
        $this->assertPageTitleContains('MyBlog Admin - My profile - JAW v1.0');

        $form = $crawler->selectButton('updateProfileSubmitButton')->form();
        $values = $form->getValues();

        static::assertEquals($expectedUserFullName, $values['user[fullName]']);
        static::assertEquals($expectedUserEmail, $values['user[email]']);
    }

    private function changeFullNameAndEmail(KernelBrowser $client, string $newFullName, string $newEmail, string $password): void
    {
        $client->followRedirects();
        $crawler = $client->request('GET', self::$profileUrl);
        $this->assertPageTitleContains('MyBlog Admin - My profile - JAW v1.0');

        $form = $crawler->selectButton('updateProfileSubmitButton')->form([
            'user[fullName]' => $newFullName,
            'user[email]' => $newEmail,
            'user[currentPassword]' => $password,
        ]);

        $client->submit($form);

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertPageTitleContains('MyBlog Admin - My profile - JAW v1.0');

        $client->followRedirects(false);
    }

    private function changePassword(KernelBrowser $client, string $currentPassword, string $newPassword): void
    {
        $client->followRedirects();
        $crawler = $client->request('GET', self::$changePasswordUrl);
        $this->assertPageTitleContains('MyBlog Admin - Change my password - JAW v1.0');

        $form = $crawler->selectButton('updatePasswordSubmitButton')->form([
            'change_password[currentPassword]' => $currentPassword,
            'change_password[newPassword][first]' => $newPassword,
            'change_password[newPassword][second]' => $newPassword,
        ]);

        $client->submit($form);

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertPageTitleContains('MyBlog Admin - Change my password - JAW v1.0');

        $client->followRedirects(false);
    }
}
