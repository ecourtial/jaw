<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Api;

use App\Tests\Functional\UserPaths\Api\Sections\ApiConfigurationTrait;
use App\Tests\Functional\UserPaths\Api\Sections\ApiPostTrait;
use App\Tests\Functional\UserPaths\Api\Sections\ApiUserPathTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiPathTrait
{
    use ApiConfigurationTrait;
    use ApiPostTrait;
    use ApiUserPathTrait;

    public function checkApiRoutes(KernelBrowser $client): void
    {
        // Configuration
        $this->hasNoAccessToConfigurationEndpoint($client);
        $this->hasAccessToConfigurationEndpoint($client);

        // Posts
        $this->hasNoAccessToGetPost($client);
        $this->checkGetPostNotFound($client);
        $this->checkErrorWhenTooManyFilters($client);
        $this->checkErrorWhenUnsupportedFilters($client);
        $this->checkCanAccessToGetPost($client);

        // Users
        $this->checkCannotAccessUser($client);
        $this->checkCannotAccessUserBecauseConnectedUserIsNotAdmin($client);
        $this->checkUserNotFound($client);
        $this->checkUserFoundWithAdminUserConnected($client);
    }
}
