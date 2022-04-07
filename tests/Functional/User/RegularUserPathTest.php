<?php

namespace App\Tests\Functional\User;

use App\Tests\Functional\Tools\Traits\ConfigurationScreenTrait;
use App\Tests\Functional\Tools\Traits\UserLoginTrait;
use App\Tests\Functional\Tools\Traits\UserProfileTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegularUserPathTest extends WebTestCase
{
    use UserLoginTrait;
    use UserProfileTrait;
    use ConfigurationScreenTrait;

    public function testRegularUserPath(): void
    {
        $client = static::createClient();

        $username = 'some_username_not_admin';
        $userPassword = 'somePassword';

        // Test login sequences
        $this->loginFailureBecauseOfBadCredentials($client, 'wrong_username', $userPassword);
        $this->loginFailureBecauseOfBadCredentials($client, $username, 'wrong_password');
        $this->loginFailureBecauseOfBadCaptchaAnswer($client, $username, $userPassword);
        $this->loginWithSuccess($client, $username, $userPassword);

        // Test change user data in profile
        $token = $this->getToken($client);
        $newEmail = 'fooRegular@stuff.com';
        $newFullName = 'Pepe the Pew';
        $newPassword = 'OhLookAtThat!';

        $this->gotoProfileAndCheckUserFullNameAndEmail($client, 'Foofoo BARBAR', 'foofoo@barbar.com');
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

        // Test change blog configuration
        $this->assertCannotAccessConfigurationPanel($client);
    }
}
