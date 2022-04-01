<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractAdminController
{
    private Request $request;
    private EntityManagerInterface $entityManager;

    public function __construct(
        string $appVersion,
        string $appName,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($appVersion, $appName);
        $this->request = $requestStack->getCurrentRequest() ?? throw new \RuntimeException('Main request cannot be null');
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/profile', methods: ['GET', 'POST'], name: 'profile')]
    public function __invoke(): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('profile');
        }


        return $this->generateView(
            'admin/user/profile.html.twig',
            'My profile',
            "My profile",
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }
}
