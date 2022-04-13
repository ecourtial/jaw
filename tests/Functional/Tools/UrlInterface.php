<?php

namespace App\Tests\Functional\Tools;

use App\Exception\Security\InvalidCaptchaException;
use App\Google\CaptchaChecker;

interface UrlInterface
{
    public const ADMIN_URL = 'http://localhost/admin';
    public const CONFIGURATION_SCREEN_URL = 'http://localhost/admin/configuration';
    public const PROFILE_SCREEN_URL = 'http://localhost/admin/profile';
    public const CHANGE_PASSWORD_SCREEN_URL = 'http://localhost/admin/password';
    public const CATEGORIES_LIST_SCREEN_URL = 'http://localhost/admin/category/list';
    public const CATEGORIES_DETAIL_SCREEN_URL_ROOT = 'http://localhost/admin/category/';
}
