<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Admin\Sections;

use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait UserProfileTrait
{
    protected function checkEditProfileMenuItem(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', UrlInterface::ADMIN_URL);
        $link = $crawler->selectLink('Edit my profile')->link();
        $client->click($link);
        $this->assertPageTitleContains('MyBlog Admin - My profile - JAW v1.0');
    }

    protected function checkChangePasswordMenuItem(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', UrlInterface::ADMIN_URL);
        $link = $crawler->selectLink('Change my password')->link();
        $client->click($link);
        $this->assertPageTitleContains('MyBlog Admin - Change my password - JAW v1.0');
    }

    protected function getToken(KernelBrowser $client): string
    {
        $crawler = $client->request('GET', UrlInterface::PROFILE_SCREEN_URL);
        $divContent = $crawler->filter('#userToken')->text();

        return trim(explode(': ', $divContent)[1]);
    }

    protected function gotoProfileAndCheckUserFullNameAndEmail(
        KernelBrowser $client,
        string $expectedUserFullName,
        string $expectedUserEmail
    ): void {
        $crawler = $client->request('GET', UrlInterface::PROFILE_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - My profile - JAW v1.0');

        $form = $crawler->selectButton('updateProfileSubmitButton')->form();
        $values = $form->getValues();

        static::assertEquals($expectedUserFullName, $values['user[fullName]']);
        static::assertEquals($expectedUserEmail, $values['user[email]']);
    }

    protected function changeFullNameAndEmail(KernelBrowser $client, string $newFullName, string $newEmail, string $password): void
    {
        $client->followRedirects();
        $crawler = $client->request('GET', UrlInterface::PROFILE_SCREEN_URL);
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

    protected function changePassword(KernelBrowser $client, string $currentPassword, string $newPassword): void
    {
        $client->followRedirects();
        $crawler = $client->request('GET', UrlInterface::CHANGE_PASSWORD_SCREEN_URL);
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
