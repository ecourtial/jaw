<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\ConfigurationType;
use App\Service\ConfigurationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Configuration extends AbstractAdminController
{
    #[Route('/admin/configuration', methods: ['GET', 'POST'], name: 'configuration')]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(ConfigurationService $configurationService): Response
    {
        $configuration = $configurationService->get();

        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $configurationService->save($configuration);
                $this->addFlash('success', 'configuration.updated_successfully');
            } catch (\Throwable $exception) {
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
