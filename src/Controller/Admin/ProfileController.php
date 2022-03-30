<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractAdminController
{
    #[Route('/admin/profile', methods: ['GET', 'POST'], name: 'profile')]
    public function __invoke(): Response
    {
        return $this->generateView(
            'admin/user/profile.html.twig',
            'My profile',
            "My profile",
        );
    }
}
