<?php

namespace App\Tests\Functional\TestingTools;

use App\Exception\Security\InvalidCaptchaException;
use App\Google\CaptchaChecker;

interface UrlInterface
{
    // ADMIN
    public const ADMIN_URL = 'http://localhost/admin';
    public const CONFIGURATION_SCREEN_URL = 'http://localhost/admin/configuration';
    public const PROFILE_SCREEN_URL = 'http://localhost/admin/profile';
    public const CHANGE_PASSWORD_SCREEN_URL = 'http://localhost/admin/password';
    public const CATEGORIES_LIST_SCREEN_URL = 'http://localhost/admin/category';
    public const POSTS_LIST_URL = 'http://localhost/admin/post';

    // API

    public const CONFIGURATION_ENDPOINT_URL = 'http://localhost/api/v1/configuration';
    public const GET_POST_ENDPOINT_URL = 'http://localhost/api/v1/post?';
    public const GET_SEARCH_POST_ENDPOINT_URL = 'http://localhost/api/v1/post/search?';
    public const GET_CATEGORY_ENDPOINT_URL = 'http://localhost/api/v1/category?';
    public const GET_SEARCH_CATEGORY_ENDPOINT_URL = 'http://localhost/api/v1/category/search?';
    public const GET_USER_ENDPOINT_URL = 'http://localhost/api/v1/user';
}
