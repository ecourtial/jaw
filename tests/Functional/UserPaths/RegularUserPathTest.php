<?php

namespace App\Tests\Functional\UserPaths;

use App\Tests\Functional\UserPaths\Subsets\CategoriesPathTrait;
use App\Tests\Functional\UserPaths\Subsets\PostsPathTrait;
use App\Tests\Functional\UserPaths\Subsets\UserPathCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegularUserPathTest extends WebTestCase
{
    use UserPathCaseTrait;
    use CategoriesPathTrait;
    use PostsPathTrait;

    public function testRegularUserPath(): void
    {
        $client = static::createClient();

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

        // Keep that in last position
        $this->logout($client);
    }
}
