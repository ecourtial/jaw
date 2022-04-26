<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Admin\Sections;

use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait ConfigurationScreenTrait
{
    protected function checkConfigurationMenuItem(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', UrlInterface::ADMIN_URL);
        $link = $crawler->selectLink('Configuration')->link();
        $client->click($link);
        $this->assertPageTitleContains('MyBlog Admin - Edit configuration - JAW v1.0');
    }

    protected function gotoConfigurationScreenAndCheckData(
        KernelBrowser $client,
        string $expectedBlogTitle,
        string $expectedBlogDescription,
        string $expectedCopyrightMessage,
        string $expectedCopyrightExtraMessage,
        string $expectedLinkedinUserName,
        string $expectedGithubUserName,
        string $expectedGoogleAnalyticsId,
    ): void {
        $crawler = $client->request('GET', UrlInterface::CONFIGURATION_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - Edit configuration - JAW v1.0');

        $form = $crawler->selectButton('updateConfigurationSubmitButton')->form();
        $values = $form->getValues();

        static::assertEquals($expectedBlogTitle, $values['configuration[blogTitle]']);
        static::assertEquals($expectedBlogDescription, $values['configuration[blogDescription]']);
        static::assertEquals($expectedCopyrightMessage, $values['configuration[copyrightMessage]']);
        static::assertEquals($expectedCopyrightExtraMessage, $values['configuration[copyrightExtraMessage]']);
        static::assertEquals($expectedLinkedinUserName, $values['configuration[linkedinUsername]']);
        static::assertEquals($expectedGithubUserName, $values['configuration[githubUsername]']);
        static::assertEquals($expectedGoogleAnalyticsId, $values['configuration[googleAnalyticsId]']);
    }

    protected function gotoConfigurationScreenAndUpdateData(
        KernelBrowser $client,
        string $userPassword,
        string $newBlogTitle,
        string $newBlogDescription,
        string $newCopyrightMessage,
        string $newCopyrightExtraMessage,
        string $newLinkedinUserName,
        string $newGithubUserName,
        string $newGoogleAnalyticsId,
    ): void {
        $client->followRedirects();

        $crawler = $client->request('GET', UrlInterface::CONFIGURATION_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - Edit configuration - JAW v1.0');

        $form = $crawler->selectButton('updateConfigurationSubmitButton')->form([
            'configuration[blogTitle]' => $newBlogTitle,
            'configuration[blogDescription]' => $newBlogDescription,
            'configuration[copyrightMessage]' => $newCopyrightMessage,
            'configuration[copyrightExtraMessage]' => $newCopyrightExtraMessage,
            'configuration[linkedinUsername]' => $newLinkedinUserName,
            'configuration[githubUsername]' => $newGithubUserName,
            'configuration[googleAnalyticsId]' => $newGoogleAnalyticsId,
            'configuration[currentPassword]' => $userPassword,
        ]);

        $client->submit($form);

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertPageTitleContains('MyBlog Admin - Edit configuration - JAW v1.0');

        $client->followRedirects(false);
    }

    protected function assertCannotAccessConfigurationPanel(KernelBrowser $client): void
    {
        $client->request('GET', UrlInterface::CONFIGURATION_SCREEN_URL);
        static::assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
