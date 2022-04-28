<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractJawController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractJawController
{
    #[Route('/login', name: 'security_login')]
    public function login(string $recaptchaPublicKey, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_home');
        }

        return $this->generateView(
            'admin/user/login_form.html.twig',
            $this->appName . ' - ' . $this->translator->trans('user.login.title'),
            $this->translator->trans('user.login.title'),
            [
                'recaptcha_key' => $recaptchaPublicKey,
                'last_username' => $authenticationUtils->getLastUsername(),
                'error' => $authenticationUtils->getLastAuthenticationError()
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
