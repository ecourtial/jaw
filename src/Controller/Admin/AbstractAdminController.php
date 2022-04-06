<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAdminController extends AbstractController
{
    protected string $appVersion;
    protected string $appName;

    public function __construct(string $appVersion, string $appName)
    {
        $this->appVersion = $appVersion;
        $this->appName = $appName;
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
