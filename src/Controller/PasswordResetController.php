<?php

namespace App\Controller;

use App\Form\PasswordResetRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class PasswordResetController extends AbstractController
{
    /**
     * @Route("/forgot-password", name="app_forgot_password")
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    public function forgotPassword(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Formulaire pour entrer le nom d'utilisateur ou l'identifiant unique.
        $form = $this->createForm(PasswordResetRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $userRepository->findOneBy(['username' => $data['username']]); // Recherche par nom d'utilisateur.

            if ($user) {
                // Génération d'un jeton unique et stockage dans la base de données.
                $token = bin2hex(random_bytes(32));
                $user->setResetToken($token);
                $user->setTokenExpiryDate(new \DateTime('+1 hour'));
                // Durée de validité de 1 heure.
                $entityManager->flush();

                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            } else {
                $this->addFlash('danger', 'Utilisateur introuvable.');
            }

            return $this->redirectToRoute('app_reset_password');
        }

        return $this->render('security/request_reset_password.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset-password/{token}", name="app_reset_password")
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param string $token
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $passwordHasher
     * 
     * @return Response
     */
    public function resetPassword(Request $request, UserRepository $userRepository, string $token, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Recherche de l'utilisateur avec le jeton fourni.
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user || $user->isTokenExpired() === true) {
            $this->addFlash('danger', 'Le jeton de réinitialisation est invalide ou expiré.');

            return $this->redirectToRoute('app_forgot_password');
        }

        // Formulaire pour entrer le nouveau mot de passe.
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();

            // Encodage et mise à jour du mot de passe.
            $user->setPassword(
                $passwordHasher->hashPassword($user, $newPassword)
            );

            // Réinitialisation du jeton pour éviter sa réutilisation.
            $user->setResetToken(null);
            $user->setTokenExpiryDate(null);

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
