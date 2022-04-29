<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/post')]
class PostController extends AbstractFilteredResultApiController
{
    #[Route('', methods: ['GET'], name: 'api_post_get')]
    public function getPost(PostRepository $postRepository): JsonResponse
    {
        return $this->getResultForUniqueFilter($postRepository);
    }
}
