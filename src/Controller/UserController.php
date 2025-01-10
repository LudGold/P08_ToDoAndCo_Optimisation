<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/admin/users', name: 'admin_user_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour accéder à cette page')]
    public function listUsers(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/create', name: 'app_user_create', methods: ['GET', 'POST'])]
    public function createUser(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Vérifier si l'utilisateur est déjà connecté
    if ($this->getUser()) {
        $this->addFlash('error', 'Vous êtes déjà connecté(e) et ne pouvez pas créer un nouvel utilisateur.');
        return $this->redirectToRoute('app_homepage');
    }
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'is_edit'      => false,
            'is_admin'     => $this->isGranted('ROLE_ADMIN'),
            'is_self_edit' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hashage du mot de passe
            $plainPassword  = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Attribution du rôle utilisateur par défaut
            $user->setRoles(['ROLE_USER']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->logger->info('Un utilisateur a été créé', [
                'user_id'    => $user->getId(),
                'username'   => $user->getUsername(),
                'created_by' => $this->security->getUser()?->getUserIdentifier(),
            ]);

            $this->addFlash('success', 'Votre compte a été créé avec succès.');

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function editUser(
        User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $isSelfEdit  = $currentUser && $currentUser->getId() === $user->getId();
        $isAdmin     = $this->isGranted('ROLE_ADMIN');

        if (!$isAdmin && !$isSelfEdit) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce profil.');
        }

        $form = $this->createForm(UserType::class, $user, [
            'is_edit'      => true,
            'is_admin'     => $isAdmin,
            'is_self_edit' => $isSelfEdit,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des droits pour la modification des rôles
            if ($form->has('roles')) {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
            }

            // Gestion du mot de passe
            if ($form->has('password')) {
                $plainPassword = $form->get('password')->getData();
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }
            }

            $this->entityManager->flush();

            $this->logger->info('Un utilisateur a été modifié', [
                'user_id'     => $user->getId(),
                'username'    => $user->getUsername(),
                'modified_by' => $this->security->getUser()?->getUserIdentifier(),
            ]);

            $this->addFlash('success', 'Le profil a été modifié avec succès.');

            return $this->redirectToRoute(
                $isAdmin ? 'admin_user_list' : 'app_homepage'
            );
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
