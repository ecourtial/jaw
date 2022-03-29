<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\EventSubscriber;

class UserLoginSubscriber
{
    // Here before auth check the google captcha
    // If captcha invalid, or any other error, fill properly the message in to the session
    // so it will be displayed in the login form.
}
