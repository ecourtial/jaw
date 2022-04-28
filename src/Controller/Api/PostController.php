<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractJawController;
use App\Repository\PostRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/post')]
class PostController extends AbstractJawController
{
    #[Route('', methods: ['GET'], name: 'api_get_post')]
    public function getPost(PostRepository $postRepository): JsonResponse
    {
        $queryParamsCount = count($this->request->query);
        if ($queryParamsCount > 1) {
            return new JsonResponse(['message' => "Only one filter can be accepted, $queryParamsCount given."], 401);
        }

        $id = (int)$this->request->get('id');
        $slug = $this->request->get('slug', '');

        $filter = '';
        $param = '';

        if ($id !== 0) {
            $filter = 'id';
            $param = $id;
        }

        if ($slug !== '') {
            $filter = 'slug';
            $param = $slug;
        }

        try {
            return new JsonResponse($postRepository->getByApiFilter($filter, $param));
        } catch (NoResultException $exception) {
            return new JsonResponse(['message' => 'No result found'], 404);
        } catch (\LogicException $exception) {
            return new JsonResponse(['message' => 'No supported filter was given. Available filters are: id, slug.'], 401);
        }
    }
}
