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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Security;


class UserController extends AbstractController
{

    /**
     * @Route("admin/users", name="admin_user_list")
     * @IsGranted("ROLE_ADMIN")
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
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);

        $form->handleRequest($request);

        // Validation du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Ajoute ROLE_USER par défaut à chaque création d'utilisateur
            $user->setRoles(array_unique(array_merge($user->getRoles(), ['ROLE_USER'])));

            // Encodage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Sauvegarde de l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Log de la création de l'utilisateur
            $logger->info('Un utilisateur a été créé', ['user_id' => $user->getId()]);

            // Message de confirmation
            $this->addFlash('success', "Vous avez bien été ajouté.");

            // Redirection vers la liste des utilisateurs
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/{id}/edit", name="app_user_edit")
     */
    public function editUser(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur connecté n'est pas administrateur et essaie de modifier un autre utilisateur
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'avez pas la permission de modifier cet utilisateur.");
        }
        // Si l'utilisateur est un administrateur, il peut modifier les rôles , sinon on bloque certains champs
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        // Vérifie si l'utilisateur connecté modifie son propre profil
        $isSelfEdit = ($this->getUser() === $user);

        // Si ce n'est pas son propre profil et qu'il n'est pas admin
        if (!$isAdmin && !$isSelfEdit) {
            throw $this->createAccessDeniedException("Vous n'avez pas la permission de modifier cet utilisateur.");
        }

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true,
            'is_admin' => $isAdmin,
            'is_self_edit' => $isSelfEdit,
        ]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'utilisateur change son mot de passe, on le hash
            if ($user->getPassword()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // Ajout d'un message flash de confirmation
            $this->addFlash('success', 'Votre profil a été mis à jour.');

            // Redirection en fonction de l'utilisateur ou de l'administrateur
            if ($isAdmin && !$isSelfEdit) {
                return $this->redirectToRoute('admin/users'); // Redirection vers la gestion des utilisateurs pour l'admin
            }

            return $this->redirectToRoute('/'); // homepage après validation du profil
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
