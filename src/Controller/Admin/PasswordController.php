<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasswordController extends AbstractAdminController
{
    private Request $request;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private TranslatorInterface $translator;

    public function __construct(
        string $appVersion,
        string $appName,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        TranslatorInterface $translator,
    ) {
        parent::__construct($appVersion, $appName);
        $this->request = $requestStack->getCurrentRequest() ?? throw new \RuntimeException('Main request cannot be null');
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->translator = $translator;
    }

    #[Route('/admin/password', methods: ['GET', 'POST'], name: 'password_change')]
    public function __invoke(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser() ?? throw new AuthenticationException('User not found');

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            /** @var string $newPassword */
            $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('user.password_updated_successfully'));

            return $this->redirectToRoute('password_change');
        }

        return $this->generateView(
            'admin/user/change_password.html.twig',
            'Change my password',
            "Change my password",
            [
                'form' => $form->createView(),
            ]
        );
    }
}
