<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/admin/users", name="admin_user_list")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param UserRepository $userRepository
     * 
     * @return Response
     */
    public function listAction(UserRepository $userRepository): Response
    {
        // Récupérer tous les utilisateurs.
        $users = $userRepository->findAll();

        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/create", name="app_user_create")
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    public function createAction(Request $request, UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);

        $form->handleRequest($request);

        // Validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Ajoute ROLE_USER par défaut à chaque création d'utilisateur.
            $user->setRoles(array_unique(array_merge($user->getRoles(), ['ROLE_USER'])));

            // Encodage du mot de passe.
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Sauvegarde de l'utilisateur dans la base de données.
            $entityManager->persist($user);
            $entityManager->flush();

            // Log de la création de l'utilisateur.
            $logger->info('Un utilisateur a été créé', ['user_id' => $user->getId()]);

            // Message de confirmation.
            $this->addFlash('success', 'Vous avez bien été ajouté.');

            // Redirection vers la liste des utilisateurs.
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/{id}/edit", name="app_user_edit")
     *
     * @param User $user
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * 
     * @return Response
     */
    public function editAction(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger, EntityManagerInterface $entityManager): Response
    {
        // Modification d'un utilisateur.
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        // Validation et traitement du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Encodage du mot de passe si nécessaire.
            if ($user->getPassword()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // Log de la modification de l'utilisateur.
            $logger->info('Un utilisateur a été modifié', ['user_id' => $user->getId()]);

            // Message de confirmation.
            $this->addFlash('success', "L'utilisateur a bien été modifié.");

            // Redirection vers la liste des utilisateurs.
            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
