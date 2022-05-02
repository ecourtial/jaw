<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractJawController;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractJawController
{
    #[Route('/admin/profile', methods: ['GET', 'POST'], name: 'profile')]
    public function __invoke(UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->beginTransaction();
            try {
                // @phpstan-ignore-next-line
                $userRepository->save($user);
                $this->entityManager->commit();

                $this->addFlash('success', 'user.updated_successfully');
            } catch (\Throwable $exception) {
                $this->entityManager->rollback();
                $this->logger->log(LogLevel::ERROR, 'Impossible to update the user.', ['exception' => $exception]);
                $this->addFlash('alert', 'generic_error_message');
            }

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
