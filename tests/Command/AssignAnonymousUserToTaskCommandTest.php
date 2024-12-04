<?php

namespace App\tests\Command;

use App\Command\AssignAnonymousUserToTaskCommand;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignAnonymousUserToTaskCommandTest extends KernelTestCase
{
    private $taskRepository;
    private $entityManager;
    private $input;
    private $output;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    
    }

    public function testConstruct(): void
    {
        $command = new AssignAnonymousUserToTaskCommand($this->taskRepository, $this->entityManager);

        $this->assertInstanceOf(AssignAnonymousUserToTaskCommand::class, $command);


    }

    public function testConfigure(): void
    {
        $command = new AssignAnonymousUserToTaskCommand($this->taskRepository, $this->entityManager);

        $this->assertSame('app:assign-anonymous-to-tasks', $command->getName());
        $this->assertSame('Assigner l\'utilisateur "anonyme" à toutes les tâches sans auteur.', $command->getDescription());
    }

    public function testExecuteFailsWhenAnonymousUserNotFound(): void
    {
        // Mock du repository pour retourner null pour l'utilisateur "anonyme"
        $this->entityManager->method('getRepository')
            ->willReturnCallback(function ($entityClass) {
                if ($entityClass === User::class) {
                    return $this->createConfiguredMock(TaskRepository::class, [
                        'findOneBy' => null, // Simule l'absence de l'utilisateur "anonyme"
                    ]);
                }
            });

        // Instanciation de la commande
        $command = new AssignAnonymousUserToTaskCommand($this->taskRepository, $this->entityManager);

        // Testeur de commande
        $application = new Application();
        $application->add($command);
        $commandTester = new CommandTester($command);

        // Exécution de la commande
        $commandTester->execute([]);

        // Vérification que la commande retourne une erreur
        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('L\'utilisateur "anonyme" n\'existe pas.', $commandTester->getDisplay());
    }

    public function testExecuteAssignsAnonymousUserToTasks(): void
{
    // Création de l'utilisateur anonyme pour le test
    $anonymousUser = new User();
    $anonymousUser->setUsername('anonyme');

    // Création d'une tâche sans auteur pour le test
    $taskWithoutAuthor = new Task();

    // Configuration du TaskRepository (celui injecté dans la commande)
    $this->taskRepository
        ->method('findBy')
        ->with(['author' => null])
        ->willReturn([$taskWithoutAuthor]);

    // Configuration du mock de l'EntityManager pour retourner l'utilisateur anonyme
    $userRepository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
    $userRepository->method('findOneBy')
        ->with(['username' => 'anonyme'])
        ->willReturn($anonymousUser);

    $this->entityManager
        ->method('getRepository')
        ->with(User::class)  // On précise qu'on attend User::class
        ->willReturn($userRepository);

    // On s'attend à ce que flush soit appelé une fois
    $this->entityManager
        ->expects($this->once())
        ->method('flush');

    // Instanciation de la commande
    $command = new AssignAnonymousUserToTaskCommand($this->taskRepository, $this->entityManager);

    // Création du testeur de commande
    $application = new Application();
    $application->add($command);
    $commandTester = new CommandTester($command);

    // Exécution de la commande
    $commandTester->execute([]);

    // Vérifications
    $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    $this->assertStringContainsString(
        'Toutes les tâches sans auteur ont été assignées',
        $commandTester->getDisplay()
    );
}
public function testExecuteSuccessCommand(): void
{
    // Création de l'utilisateur anonyme
    $anonymousUser = new User();
    $anonymousUser->setUsername('anonyme');

    // Création d'une tâche sans auteur
    $taskWithoutAuthor = new Task();

    // Configuration du TaskRepository pour retourner une tâche sans auteur
    $this->taskRepository
        ->method('findBy')
        ->with(['author' => null])
        ->willReturn([$taskWithoutAuthor]);

    // Configuration du mock de l'EntityManager pour retourner l'utilisateur anonyme
    $userRepository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
    $userRepository->method('findOneBy')
        ->with(['username' => 'anonyme'])
        ->willReturn($anonymousUser);

    $this->entityManager
        ->method('getRepository')
        ->with(User::class)
        ->willReturn($userRepository);

    // On s'attend à ce que flush soit appelé une fois
    $this->entityManager
        ->expects($this->once())
        ->method('flush');

    // Instanciation de la commande
    $command = new AssignAnonymousUserToTaskCommand($this->taskRepository, $this->entityManager);

    // Testeur de commande
    $application = new Application();
    $application->add($command);
    $commandTester = new CommandTester($command);

    // Exécution de la commande
    $commandTester->execute([]);

    // Vérifications
    $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    $this->assertStringContainsString(
        'Toutes les tâches sans auteur ont été assignées',
        $commandTester->getDisplay()
    );
}

}