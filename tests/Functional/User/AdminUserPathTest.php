<?php
/**
 * - Changes email and username.
 * - Read the token.
 * - Change password
 * - Logout.
 * - Connects again with the new credentials.
 * - Check that the token has not changed.
 * - Try to access configuration panel: change all the values
 * - Logout.
 * - Connect again.
 * - Check the configuration values: they have been properly updated
 */

namespace App\Tests\Functional\User;

use App\Tests\Functional\Tools\Traits\UserLoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUserPathTest extends WebTestCase
{
    use UserLoginTrait;

    public function testAdminUserPath(): void
    {
        $client = static::createClient();

        $username = 'some_username_admin';
        $userPassword = 'somePassword';

        $this->loginFailureBecauseOfBadCredentials($client);
        $this->loginFailureBecauseOfBadCaptchaAnswer($client, $username, $userPassword);
        $this->loginWithSuccess($client, $username, $userPassword);
    }
}
