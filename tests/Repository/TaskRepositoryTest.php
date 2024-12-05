<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $taskRepository;
    private $user;
    private $tasksToClean = [];  // Pour garder trace des tâches à nettoyer

    protected function setUp(): void
    {
        // Démarrage du kernel
        self::bootKernel();

        // Récupération de l'entity manager
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        // Récupération du repository
        $this->taskRepository = $this->entityManager->getRepository(Task::class);

        // Création d'un utilisateur de test
        $this->user = new User();
        $this->user->setUsername('testUser');
        $this->user->setPassword('password123');
        $this->user->setEmail('test'.uniqid().'@test.com');

        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        // Nettoyage dans le bon ordre : d'abord les tasks, puis l'user
        if (!empty($this->tasksToClean)) {
            foreach ($this->tasksToClean as $task) {
                $this->entityManager->remove($task);
            }
            $this->entityManager->flush();
            $this->tasksToClean = [];
        }

        if ($this->user) {
            $this->entityManager->remove($this->user);
            $this->entityManager->flush();
        }

        $this->entityManager->close();
        $this->entityManager = null;
        parent::tearDown();
    }

    public function testAdd(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setContent('Test Content');
        $task->setCreatedAt(new \DateTime());
        $task->setAuthor($this->user);

        $this->tasksToClean[] = $task;  // Ajouter à la liste de nettoyage

        // Test sans flush
        $this->taskRepository->add($task, false);
        $this->assertTrue($this->entityManager->contains($task));

        $task2 = new Task();
        $task2->setTitle('Test Task 2');
        $task2->setContent('Test Content 2');
        $task2->setCreatedAt(new \DateTime());
        $task2->setAuthor($this->user);

        $this->tasksToClean[] = $task2;  // Ajouter à la liste de nettoyage

        // Test avec flush
        $this->taskRepository->add($task2, true);

        $foundTask2 = $this->taskRepository->find($task2->getId());
        $this->assertNotNull($foundTask2);
        $this->assertEquals('Test Task 2', $foundTask2->getTitle());
    }

    public function testRemove(): void
    {
        $task = new Task();
        $task->setTitle('Task to remove');
        $task->setContent('Content to remove');
        $task->setCreatedAt(new \DateTime());
        $task->setAuthor($this->user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $taskId = $task->getId();

        // Test sans flush
        $this->taskRepository->remove($task, false);
        $this->assertFalse($this->entityManager->contains($task));

        $task2 = new Task();
        $task2->setTitle('Task to remove 2');
        $task2->setContent('Content to remove 2');
        $task2->setCreatedAt(new \DateTime());
        $task2->setAuthor($this->user);

        $this->entityManager->persist($task2);
        $this->entityManager->flush();
        $task2Id = $task2->getId();

        // Test avec flush
        $this->taskRepository->remove($task2, true);

        $foundTask2 = $this->taskRepository->find($task2Id);
        $this->assertNull($foundTask2);
    }

    public function testFindTask(): void
    {
        $task = new Task();
        $task->setTitle('Task to find');
        $task->setContent('Content to find');
        $task->setCreatedAt(new \DateTime());
        $task->setAuthor($this->user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->tasksToClean[] = $task;  // Ajouter à la liste de nettoyage

        $foundTask = $this->taskRepository->find($task->getId());

        $this->assertNotNull($foundTask);
        $this->assertEquals('Task to find', $foundTask->getTitle());
        $this->assertEquals('Content to find', $foundTask->getContent());
        $this->assertEquals($this->user, $foundTask->getAuthor());
    }
}
