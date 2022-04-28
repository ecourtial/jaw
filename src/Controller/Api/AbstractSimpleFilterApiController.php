<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractJawController;
use App\Repository\ApiFilterableResultInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\JsonResponse;

class AbstractSimpleFilterApiController extends AbstractJawController
{
    public function getResult(ApiFilterableResultInterface $repository): JsonResponse
    {
        $queryParamsCount = count($this->request->query);
        if ($queryParamsCount > 1) {
            return new JsonResponse(['message' => "Only one filter can be accepted, $queryParamsCount given."], 400);
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
            return new JsonResponse($repository->getByApiFilter($filter, $param));
        } catch (NoResultException $exception) {
            return new JsonResponse(['message' => 'No result found.'], 404);
        } catch (\LogicException $exception) {
            return new JsonResponse(['message' => 'No supported filter was given. Available filters are: id, slug.'], 400);
        }
    }
}
