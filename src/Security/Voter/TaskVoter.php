<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class TaskVoter extends Voter
{
    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['TASK_EDIT', 'TASK_DELETE'])
            && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Task $task */
        $task = $subject;

        switch ($attribute) {
            case 'TASK_EDIT':
                // Pour l'édition : admin peut tout éditer, user peut éditer ses tâches
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    
                    return true;
                }
                return $task->getAuthor() === $user;

            case 'TASK_DELETE':
                // Pour la suppression : seul le propriétaire peut supprimer
                return $task->getAuthor() === $user;
        }

        return false;
    }
}
