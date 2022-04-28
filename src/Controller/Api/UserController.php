<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/user')]
class UserController extends AbstractController
{
    #[Route('/{id<\d+>}', methods: ['GET'], name: 'api_user_get')]
    public function getConfig(User $user = null): JsonResponse
    {
        if (false === \in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return new JsonResponse(['message' => 'Route only accessible to admin users.'], 403);
        }

        if (null === $user) {
            return new JsonResponse(['message' => 'User not found.'], 404);
        }

        $response = [
            'id' => $user->getId(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $user->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'username' => $user->getUsername(),
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'token' => $user->getToken(),
        ];

        return new JsonResponse($response);
    }
}
