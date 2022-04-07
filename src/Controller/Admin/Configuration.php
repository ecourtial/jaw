<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\ConfigurationType;
use App\Repository\ConfigurationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Configuration extends AbstractAdminController
{
    private Request $request;
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        string $appVersion,
        string $appName,
        RequestStack $requestStack,
        ConfigurationRepository $configurationRepository
    ) {
        parent::__construct($appVersion, $appName);
        $this->request = $requestStack->getCurrentRequest() ?? throw new \RuntimeException('Main request cannot be null');

        $this->configurationRepository = $configurationRepository;
    }

    #[Route('/admin/configuration', methods: ['GET', 'POST'], name: 'configuration')]
    public function __invoke(): Response
    {
        $configuration = $this->configurationRepository->get();

        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->configurationRepository->save($configuration);

            $this->addFlash('success', 'configuration.updated_successfully');

            return $this->redirectToRoute('configuration');
        }


        return $this->generateView(
            'admin/configuration/configuration.html.twig',
            'Edit configuration',
            "Edit configuration",
            [
                'user' => $configuration,
                'form' => $form->createView(),
            ]
        );
    }
}
