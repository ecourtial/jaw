<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Search\SearchEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractAdminController
{
    #[Route('/admin/search', methods: ['POST'], name: 'search')]
    public function __invoke(SearchEngine $searchEngine): Response
    {
        $query = trim($this->request->get('query', ''));

        if ('' === $query) {
            return $this->redirectToRoute('admin_home');
        }

        return $this->generateView(
            'admin/search/result.html.twig',
            $this->translator->trans('search.result_title'),
            $this->translator->trans('search.result_title'),
            ['result' => $searchEngine->search($query)]
        );
    }
}