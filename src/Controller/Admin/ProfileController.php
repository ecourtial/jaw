<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractAdminController
{
    #[Route('/admin/profile', methods: ['GET', 'POST'], name: 'profile')]
    public function __invoke(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('profile');
        }


        return $this->generateView(
            'admin/user/profile.html.twig',
            $this->translator->trans('user.profile.title'),
            $this->translator->trans('user.profile.title'),
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }
}
