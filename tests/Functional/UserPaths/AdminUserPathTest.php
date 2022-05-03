<?php

namespace App\Tests\Functional\UserPaths;

use App\Tests\Functional\UserPaths\Admin\AdminCategoriesPathTrait;
use App\Tests\Functional\UserPaths\Admin\AdminPostsPathTrait;
use App\Tests\Functional\UserPaths\Admin\AdminSearchPathTrait;
use App\Tests\Functional\UserPaths\Admin\AdminUserPathCaseTrait;
use App\Tests\Functional\UserPaths\Api\ApiPathTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUserPathTest extends WebTestCase
{
    use AdminUserPathCaseTrait;
    use AdminCategoriesPathTrait;
    use AdminPostsPathTrait;
    use AdminSearchPathTrait;
    use ApiPathTrait;

    public function testAdminUserPath(): void
    {
        $client = static::createClient();

        /*************************************/
        /**************** API ****************/
        /*************************************/

        $this->checkApiRoutes($client);

        /*************************************/
        /************ SITE ADMIN *************/
        /*************************************/

        $newPassword = 'someNewPassword';

        // Security basics
        $this->checkStandardSecurity(
            $client,
            'some_username_admin',
            'Foo BAR',
            'foo@bar.com',
            'somePassword',
            'fooAdmin@stuff.com',
            'Admin Pepe the Pew',
            $newPassword
        );

        // Test change blog configuration
        $this->checkConfigurationMenuItem($client);

        $this->gotoConfigurationScreenAndCheckData(
            $client,
            'MyBlog',
            'My awesome blog.',
            'Do not copy my stuff.',
            'Or I will unleash my poodle.',
            'LinkedinPseudo',
            'GithubPseudo',
            '1234Abcdef',
            '',
            true,
        );

        $newBlogTitle = 'NewBlogTitle';
        $newBlogDescription = 'NewBlogDescription';
        $newBlogCopyrightMessage = 'NewBlogCopyrightMessage';
        $newBlogCopyrightExtraMessage = 'NewBlogCopyrightExtraMessage';
        $newLinkedinUsername = 'newLinkedinUsername';
        $newGithubUsername = 'newGithubUsername';
        $newGoogleAnalyticsId = 'newGoogleAnalyticsId';
        $newCallbackUrl = 'https://foo.bar';

        $this->gotoConfigurationScreenAndUpdateData(
            $client,
            $newPassword,
            $newBlogTitle,
            $newBlogDescription,
            $newBlogCopyrightMessage,
            $newBlogCopyrightExtraMessage,
            $newLinkedinUsername,
            $newGithubUsername,
            $newGoogleAnalyticsId,
            $newCallbackUrl,
            0,
        );

        $this->gotoConfigurationScreenAndCheckData(
            $client,
            $newBlogTitle,
            $newBlogDescription,
            $newBlogCopyrightMessage,
            $newBlogCopyrightExtraMessage,
            $newLinkedinUsername,
            $newGithubUsername,
            $newGoogleAnalyticsId,
            $newCallbackUrl,
            false,
        );

        // Categories
        $this->checkCategoriesPath($client);

        // Posts
        $this->checkPostsPath($client);

        // Search
        $this->searchPosts($client);

        // Keep that in last position
        $this->logout($client);
    }
}
