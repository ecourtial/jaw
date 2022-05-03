<?php

namespace App\Tests\Functional\UserPaths\Api\Sections;

use App\Entity\Post;
use App\Entity\User;
use App\Security\ApiKeyAuthenticator;
use App\Tests\Functional\TestingTools\RequestTools;
use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiUserPathTrait
{
    public function checkCannotAccessUser(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_USER_ENDPOINT_URL . '/99',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => 'nope'
            ]
        );

        static::assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function checkUserNotFound(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_USER_ENDPOINT_URL . '/99',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals(404, $client->getResponse()->getStatusCode());

        $expected = ['message' => 'User not found.'];
        static::assertEquals($expected, \json_decode($client->getResponse()->getContent(), true));
    }

    public function checkCannotAccessUserBecauseConnectedUserIsNotAdmin(KernelBrowser $client): void
    {
        $targetUserId = 2;

        $client->request(
            'GET',
            UrlInterface::GET_USER_ENDPOINT_URL . '/' . $targetUserId,
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getRegularUserToken()
            ]
        );

        static::assertEquals(403, $client->getResponse()->getStatusCode());

        $expected = ['message' => 'Route only accessible to admin users.'];
        static::assertEquals($expected, \json_decode($client->getResponse()->getContent(), true));
    }

    public function checkUserFoundWithAdminUserConnected(KernelBrowser $client): void
    {
        $targetUserId = 2;

        $client->request(
            'GET',
            UrlInterface::GET_USER_ENDPOINT_URL . '/' . $targetUserId,
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals(200, $client->getResponse()->getStatusCode());

        $user = $this->getUser($targetUserId);

        $expected = [
            'id' => $user->getId(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $user->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'username' => $user->getUsername(),
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'token' => $user->getToken(),
        ];

        static::assertEquals($expected, \json_decode($client->getResponse()->getContent(), true));
    }
}
