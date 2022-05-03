<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractJawController;
use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PasswordController extends AbstractJawController
{
    #[Route('/admin/password', methods: ['GET', 'POST'], name: 'password_change')]
    public function __invoke(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser() ?? throw new AuthenticationException('User not found');

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            /** @var string $newPassword */
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $this->entityManager->beginTransaction();
            try {
                $userRepository->save($user);
                $this->entityManager->commit();
                $this->addFlash('success', $this->translator->trans('user.password_updated_successfully'));
            } catch (\Throwable $exception) {
                $this->entityManager->rollback();
                $this->logger->log(LogLevel::ERROR, 'Impossible to change the user password.', ['exception' => $exception]);
                $this->addFlash('alert', 'generic_error_message');
            }

            return $this->redirectToRoute('password_change');
        }

        return $this->generateView(
            'admin/user/change_password.html.twig',
            $this->translator->trans('user.password_title'),
            $this->translator->trans('user.password_title'),
            [
                'form' => $form->createView(),
            ]
        );
    }
}
