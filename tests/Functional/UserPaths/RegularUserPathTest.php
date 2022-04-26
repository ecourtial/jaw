<?php

namespace App\Tests\Functional\UserPaths;

use App\Tests\Functional\UserPaths\Admin\AdminCategoriesPathTrait;
use App\Tests\Functional\UserPaths\Admin\AdminPostsPathTrait;
use App\Tests\Functional\UserPaths\Admin\AdminSearchPathTrait;
use App\Tests\Functional\UserPaths\Admin\AdminUserPathCaseTrait;
use App\Tests\Functional\UserPaths\Api\ApiConfigurationPathTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegularUserPathTest extends WebTestCase
{
    use AdminUserPathCaseTrait;
    use AdminCategoriesPathTrait;
    use AdminPostsPathTrait;
    use AdminSearchPathTrait;
    use ApiConfigurationPathTrait;

    public function testRegularUserPath(): void
    {
        $client = static::createClient();

        /*************************************/
        /**************** API ****************/
        /*************************************/

        $this->hasNoAccessToConfigurationEndpoint($client);
        $this->hasAccessToConfigurationEndpoint($client);

        /*************************************/
        /************ SITE ADMIN *************/
        /*************************************/

        // Security basics
        $this->checkStandardSecurity(
            $client,
            'some_username_not_admin',
            'Foofoo BARBAR',
            'foofoo@barbar.com',
            'somePassword',
            'fooRegular@stuff.com',
            'Regular Pepe the Pew',
            'someNewPassword'
        );

        // Test change blog configuration
        $this->assertCannotAccessConfigurationPanel($client);

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
