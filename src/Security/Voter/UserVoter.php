<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
        // Constantes pour les différentes actions
        private const EDIT_ROLES = 'EDIT_ROLES';
        private const EDIT_USER = 'EDIT_USER';

        public function __construct(
                private AuthorizationCheckerInterface $authChecker
        ) {}

        protected function supports(string $attribute, $subject): bool
        {
                // Vérifie les différents cas supportés
                return match ($attribute) {
                        self::EDIT_ROLES => is_array($subject),
                        self::EDIT_USER => $subject instanceof User,
                        default => false
                };
        }

        protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
        {
                $user = $token->getUser();

                if (!$user instanceof UserInterface) {
                        return false;
                }

                // Utilisation de match pour une meilleure lisibilité
                return match ($attribute) {
                        self::EDIT_ROLES => $this->canEditRoles($subject),
                        self::EDIT_USER => $this->canEditUser($subject, $user),
                        default => false
                };
        }

        private function canEditRoles(array $roles): bool
        {
                // Vérifie si l'utilisateur connecté a les droits pour modifier les rôles
                if ($this->authChecker->isGranted('ROLE_ADMIN')) {
                        // Admins peuvent attribuer des rôles jusqu'à ROLE_ADMIN
                        return !in_array('ROLE_SUPER_ADMIN', $roles, true);
                }

                // Si ce n'est pas un admin, il ne peut pas modifier les rôles
                return false;
        }

        private function canEditUser(User $userToEdit, UserInterface $currentUser): bool
        {
                // Les admins peuvent éditer tous les utilisateurs
                if ($this->authChecker->isGranted('ROLE_ADMIN')) {
                        return true;
                }

                // Un utilisateur peut modifier son propre profil
                return $currentUser->getUserIdentifier() === $userToEdit->getUserIdentifier();
        }
}
