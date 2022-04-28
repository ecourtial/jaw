<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractJawController extends AbstractController
{
    protected string $appVersion;
    protected string $appName;
    protected TranslatorInterface $translator;
    protected Request $request;
    protected LoggerInterface $logger;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        string $appVersion,
        string $appName,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
    ) {
        $this->appVersion = $appVersion;
        $this->appName = $appName;
        $this->translator = $translator;
        $this->request = $requestStack->getCurrentRequest() ?? throw new \RuntimeException('Main request cannot be null');
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /** @param mixed[] $parameters */
    protected function generateView(
        string $view,
        string $screenTitle,
        string $screenTabName,
        array $parameters = [],
        Response $response = null
    ): Response {
        return parent::render(
            $view,
            array_merge(
                $parameters,
                [
                    'appVersion' => $this->appVersion,
                    'appName' => $this->appName,
                    'screenTitle' => $screenTitle,
                    'screenTabName' => $screenTabName
                ]
            ),
            $response
        );
    }
}
