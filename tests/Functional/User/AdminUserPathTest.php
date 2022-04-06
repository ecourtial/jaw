<?php
/**
 * - Try to access configuration panel: change all the values
 * - Logout.
 * - Connect again.
 * - Check the configuration values: they have been properly updated
 */

namespace App\Tests\Functional\User;

use App\Tests\Functional\Tools\Traits\UserLoginTrait;
use App\Tests\Functional\Tools\Traits\UserProfileTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUserPathTest extends WebTestCase
{
    use UserLoginTrait;
    use UserProfileTrait;

    public function testAdminUserPath(): void
    {
        $client = static::createClient();

        $username = 'some_username_admin';
        $userPassword = 'somePassword';

        // Test login sequences
        $this->loginFailureBecauseOfBadCredentials($client, 'wrong_username', $userPassword);
        $this->loginFailureBecauseOfBadCredentials($client, $username, 'wrong_password');
        $this->loginFailureBecauseOfBadCaptchaAnswer($client, $username, $userPassword);
        $this->loginWithSuccess($client, $username, $userPassword);

        // Test change user data in profile
        $token = $this->getToken($client);
        $newEmail = 'foo@stuff.com';
        $newFullName = 'Pepe the Pew';
        $newPassword = 'OhLookAtThat!';

        $this->gotoProfileAndCheckUserFullNameAndEmail($client, 'Foo BAR', 'foo@bar.com');
        $this->changeFullNameAndEmail($client, $newFullName, $newEmail, $userPassword);
        $this->logout($client);
        $this->loginWithSuccess($client, $username, $userPassword);
        $this->gotoProfileAndCheckUserFullNameAndEmail($client, $newFullName, $newEmail);
        static::assertEquals($token, $this->getToken($client));

        // Test change user password
        $this->changePassword($client, $userPassword, $newPassword);
        $this->logout($client);
        $this->loginFailureBecauseOfBadCredentials($client, $username, $userPassword);
        $this->loginWithSuccess($client, $username, $newPassword);
        static::assertEquals($token, $this->getToken($client));
    }
}
