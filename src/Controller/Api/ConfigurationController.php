<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ConfigurationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/configuration')]
class ConfigurationController extends AbstractController
{
    #[Route('', methods: ['GET'], name: 'api_config')]
    public function getConfig(ConfigurationRepository $configurationRepository): JsonResponse
    {
        $configuration = $configurationRepository->get();

        return new JsonResponse([
            'title' => $configuration->getBlogTitle(),
            'description' => $configuration->getBlogDescription(),
            'callbackUrl' => $configuration->getCallbackUrl(),
            'updatedAt' => $configuration->getUpdatedAt(),
            'copyrightMessage' => $configuration->getCopyrightMessage(),
            'copyrightExtraMessage' => $configuration->getCopyrightExtraMessage(),
            'githubUserName' => $configuration->getGithubUsername(),
            'linkedinUserName' => $configuration->getLinkedinUsername(),
            'googleAnalyticsId' => $configuration->getGoogleAnalyticsId(),
        ]);
    }
}
