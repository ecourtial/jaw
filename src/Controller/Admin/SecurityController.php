<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractAdminController
{
    private AuthenticationUtils $authenticationUtils;
    private string $recaptchaPublicKey;

    public function __construct(
        string $appVersion,
        string $appName,
        string $recaptchaPublicKey,
        AuthenticationUtils $authenticationUtils
    ) {
        parent::__construct($appVersion, $appName);
        $this->recaptchaPublicKey = $recaptchaPublicKey;
        $this->authenticationUtils = $authenticationUtils;
    }

    #[Route('/login', name: 'security_login')]
    public function login(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_home');
        }

        return $this->generateView(
            'admin/user/login_form.html.twig',
            "{$this->appName} - Connection",
            'Connection',
            [
                'recaptcha_key' => $this->recaptchaPublicKey,
                'last_username' => $this->authenticationUtils->getLastUsername(),
                'error' => $this->authenticationUtils->getLastAuthenticationError()
            ]
        );
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     */
    #[Route('/logout', name: 'security_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
