<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractJawController;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractJawController
{
    #[Route('/admin/search', methods: ['POST'], name: 'search')]
    public function __invoke(PostRepository $postRepository): Response
    {
        $query = trim($this->request->get('query', ''));

        if ('' === $query) {
            return $this->redirectToRoute('admin_home');
        }

        return $this->generateView(
            'admin/search/result.html.twig',
            $this->translator->trans('search.result_title'),
            $this->translator->trans('search.result_title'),
            ['result' => $postRepository->search($query)]
        );
    }
}
