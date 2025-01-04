<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:assign-anonymous-to-tasks',
    description: 'Assigner l\'utilisateur "anonyme" à toutes les tâches sans auteur.'
)]
class AssignAnonymousUserToTaskCommand extends Command
{
    private $taskRepository;

    private $entityManager;

    public function __construct(TaskRepository $taskRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->taskRepository = $taskRepository;
        $this->entityManager  = $entityManager;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer l'utilisateur anonyme.
        $anonymousUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);

        if (!$anonymousUser) {
            $io->error('L\'utilisateur "anonyme" n\'existe pas. Exécutez les fixtures d\'abord.');

            return Command::FAILURE;
        }

        // Récupérer toutes les tâches sans auteur.
        $tasksWithoutAuthor = $this->taskRepository->findBy(['author' => null]);

        foreach ($tasksWithoutAuthor as $task) {
            $task->setAuthor($anonymousUser);
        }

        // Sauvegarder les modifications.
        $this->entityManager->flush();

        $io->success('Toutes les tâches sans auteur ont été assignées à l\'utilisateur anonyme.');

        return Command::SUCCESS;
    }
}
