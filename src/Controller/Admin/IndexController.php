<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractJawController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractJawController
{
    #[Route('/admin', methods: ['GET'], name: 'admin_home')]
    public function __invoke(): Response
    {
        return $this->generateView(
            'admin/home/body.html.twig',
            $this->translator->trans('home.title'),
            $this->translator->trans('home.tab')
        );
    }
}
