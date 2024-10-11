<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Psr\Log\LoggerInterface;

class UserController extends AbstractController
{

    /**
     * @Route("/users", name="user_list")
     */
    public function listAction(UserRepository $userRepository): Response
    {
        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();

        return $this->render('user/list.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/users/create", name="app_user_create")
     */
    public function createAction(Request $request, UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        // Validation du formulaire
        if ($form->isSubmitted() && $form->isValid()) {


            // Encodage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Sauvegarde de l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Log de la création de l'utilisateur
            $logger->info('Un utilisateur a été créé', ['user_id' => $user->getId()]);

            // Message de confirmation
            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            // Redirection vers la liste des utilisateurs
            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/{id}/edit", name="app_user_edit")
     */
    public function editAction(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger,  EntityManagerInterface $entityManager): Response
    {
        // // Création du formulaire
        // $form = $this->createForm(UserType::class, $user);
        // Modification d'un utilisateur
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        // Validation et traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Encodage du mot de passe si nécessaire
            if ($user->getPassword()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // Log de la modification de l'utilisateur
            $logger->info('Un utilisateur a été modifié', ['user_id' => $user->getId()]);

            // Message de confirmation
            $this->addFlash('success', "L'utilisateur a bien été modifié.");

            // Redirection vers la liste des utilisateurs
            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
