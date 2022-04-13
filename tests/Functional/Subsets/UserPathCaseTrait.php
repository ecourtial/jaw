<?php

namespace App\Tests\Functional\Subsets;

use App\Tests\Functional\Subsets\Sections\ConfigurationScreenTrait;
use App\Tests\Functional\Subsets\Sections\UserLoginTrait;
use App\Tests\Functional\Subsets\Sections\UserProfileTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait UserPathCaseTrait
{
    use UserLoginTrait;
    use UserProfileTrait;
    use ConfigurationScreenTrait;

    /** REMINDER: At the end the user IS connected */
    protected function checkStandardSecurity(
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
