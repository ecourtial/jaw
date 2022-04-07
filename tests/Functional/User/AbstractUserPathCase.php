<?php

namespace App\Tests\Functional\User;

use App\Tests\Functional\Tools\Traits\ConfigurationScreenTrait;
use App\Tests\Functional\Tools\Traits\UserLoginTrait;
use App\Tests\Functional\Tools\Traits\UserProfileTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractUserPathCase extends WebTestCase
{
    use UserLoginTrait;
    use UserProfileTrait;
    use ConfigurationScreenTrait;

    /** REMINDER: At the end the user is connected */
    protected function testStandardInteractions(
        KernelBrowser $client,
        string $username,
        string $userFullName,
        string $userEmail,
        string $userPassword,
        string $newEmail,
        string $newFullName,
        string $newPassword,
    ): void {
        // Test login sequences
        $this->loginFailureBecauseOfBadCredentials($client, 'wrong_username', $userPassword);
        $this->loginFailureBecauseOfBadCredentials($client, $username, 'wrong_password');
        $this->loginFailureBecauseOfBadCaptchaAnswer($client, $username, $userPassword);
        $this->loginWithSuccess($client, $username, $userPassword);

        // Test change user data in profile
        $token = $this->getToken($client);

        $this->gotoProfileAndCheckUserFullNameAndEmail($client, $userFullName, $userEmail);
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
