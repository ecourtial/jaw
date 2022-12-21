<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractJawController;
use App\Form\ConfigurationType;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractJawController
{
    #[Route('/admin/configuration', methods: ['GET', 'POST'], name: 'configuration')]
    public function __invoke(
        ConfigurationRepository $configurationRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $configuration = $configurationRepository->get();

        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->beginTransaction();
            try {
                $configurationRepository->save($configuration);
                $this->entityManager->commit();
                $this->addFlash('success', 'configuration.updated_successfully');
            } catch (\Throwable $exception) {
                $this->entityManager->rollback();
                $this->logger->log(LogLevel::ERROR, 'Impossible to save the configuration.', ['exception' => $exception]);
                $this->addFlash('alert', 'generic_error_message');
            }

            return $this->redirectToRoute('configuration');
        }

        return $this->generateView(
            'admin/configuration/configuration.html.twig',
            $this->translator->trans('configuration.title'),
            $this->translator->trans('configuration.title'),
            ['form' => $form->createView()]
        );
    }
}
