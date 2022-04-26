<?php

namespace App\Tests\Functional\UserPaths;

use App\Tests\Functional\UserPaths\Admin\CategoriesPathTrait;
use App\Tests\Functional\UserPaths\Admin\PostsPathTrait;
use App\Tests\Functional\UserPaths\Admin\SearchPathTrait;
use App\Tests\Functional\UserPaths\Admin\UserPathCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUserPathTest extends WebTestCase
{
    use UserPathCaseTrait;
    use CategoriesPathTrait;
    use PostsPathTrait;
    use SearchPathTrait;

    public function testAdminUserPath(): void
    {
        $client = static::createClient();

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
            '1234Abcdef'
        );

        $newBlogTitle = 'NewBlogTitle';
        $newBlogDescription = 'NewBlogDescription';
        $newBlogCopyrightMessage = 'NewBlogCopyrightMessage';
        $newBlogCopyrightExtraMessage = 'NewBlogCopyrightExtraMessage';
        $newLinkedinUsername = 'newLinkedinUsername';
        $newGithubUsername = 'newGithubUsername';
        $newGoogleAnalyticsId = 'newGoogleAnalyticsId';

        $this->gotoConfigurationScreenAndUpdateData(
            $client,
            $newPassword,
            $newBlogTitle,
            $newBlogDescription,
            $newBlogCopyrightMessage,
            $newBlogCopyrightExtraMessage,
            $newLinkedinUsername,
            $newGithubUsername,
            $newGoogleAnalyticsId
        );

        $this->gotoConfigurationScreenAndCheckData(
            $client,
            $newBlogTitle,
            $newBlogDescription,
            $newBlogCopyrightMessage,
            $newBlogCopyrightExtraMessage,
            $newLinkedinUsername,
            $newGithubUsername,
            $newGoogleAnalyticsId
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
