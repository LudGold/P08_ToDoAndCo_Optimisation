<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;



class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="app_login")
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
           }
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        // Cette méthode ne sera jamais exécutée.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
