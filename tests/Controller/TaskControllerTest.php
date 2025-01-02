<?php

namespace App\tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class TaskControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        // Initialiser le client HTTP
        $this->client = static::createClient();
        // Récupérer l'EntityManager
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        // Charger les fixtures
        $this->loadFixtures();
        // Initialiser la session
        $this->initSession();
    }

    private function loadFixtures(): void
    {
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application(static::$kernel);
        $application->setAutoExit(false);
        $input = new \Symfony\Component\Console\Input\StringInput('doctrine:fixtures:load --env=test --quiet --no-interaction');
        $application->run($input);
    }

    private function initSession(): void
    {
        $session = $this->client->getContainer()->get('session.factory')->createSession();
        $this->client->getContainer()->set('session', $session);
        $session->start();
    }

    private function mockCsrfToken(string $tokenId, string $tokenValue): void
    {
        $csrfTokenManager = $this->getMockBuilder(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class)
            ->getMock();

        $csrfTokenManager->method('getToken')
            ->willReturn(new \Symfony\Component\Security\Csrf\CsrfToken($tokenId, $tokenValue));

        $this->client->getContainer()->set('security.csrf.token_manager', $csrfTokenManager);
    }

    private function getRepository(string $class)
    {
        return $this->entityManager->getRepository($class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testCreateTask(): void
    {
        $user = $this->getRepository(User::class)->findOneBy([]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('form[name="task"]');

        $form = $crawler->selectButton('Créer')->form([
            'task[title]' => 'Test Task',
            'task[content]' => 'Content of the test task',
        ]);
        $this->client->submit($form);

        $task = $this->getRepository(Task::class)->findOneBy(['title' => 'Test Task']);
        $this->assertNotNull($task, 'La tâche a bien été ajoutée en base de données.');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'La tâche a été ajoutée avec succès.');
    }

    public function testEditTask(): void
    {
        $user = $this->getRepository(User::class)->findOneBy([]);
        $this->client->loginUser($user);
        $task = $this->getRepository(Task::class)->findOneBy(['author' => $user]);
        $this->assertNotNull($task, 'Aucune tâche trouvée pour cet utilisateur.');

        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Updated Task Title',
            'task[content]' => 'Updated content',
        ]);
        $this->client->submit($form);

        $updatedTask = $this->getRepository(Task::class)->find($task->getId());
        $this->assertEquals('Updated Task Title', $updatedTask->getTitle());
        $this->assertEquals('Updated content', $updatedTask->getContent());

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été modifiée.');
    }

    public function testListTask(): void
    {
        $this->client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'Liste des tâches');
    }

    public function testTaskDeletionAccessControl(): void
    {
        $this->client->followRedirects(false);

        // Configuration du token CSRF
        $tokenValue = 'valid_token';
        $csrfTokenManager = $this->getMockBuilder(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class)
            ->getMock();
        $csrfTokenManager->method('getToken')
            ->willReturn(new \Symfony\Component\Security\Csrf\CsrfToken('dummy_token', $tokenValue));
        $csrfTokenManager->method('isTokenValid')
            ->willReturn(true);
        $this->client->getContainer()->set('security.csrf.token_manager', $csrfTokenManager);

        // Récupérer l'utilisateur
        $user = $this->getRepository(User::class)->findOneBy(['email' => 'user@example.com']);
        $this->assertNotNull($user, 'L\'utilisateur test doit exister.');
        $this->client->loginUser($user);

        // Créer une tâche
        $task = new Task();
        $task->setTitle('Tâche test pour contrôle d\'accès');
        $task->setContent('Contenu de test');
        $task->setCreatedAt(new \DateTime());
        $task->setIsDone(false);
        $task->setAuthor($user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $taskId = $task->getId();

        // Faire la requête de suppression
        $this->client->request(
            'POST',
            '/tasks/' . $taskId . '/delete',
            ['_token' => $tokenValue]
        );

        // S'assurer que tout est bien vide
        $this->entityManager->clear();
        $this->entityManager->getConnection()->close();
        $this->entityManager->getConnection()->connect();

        // Vérifier directement en base
        $stmt = $this->entityManager->getConnection()
            ->prepare('SELECT COUNT(*) as count FROM task WHERE id = :id');
        $result = $stmt->executeQuery(['id' => $taskId]);
        $count = $result->fetchOne();

        $this->assertEquals(0, $count, 'La tâche devrait être supprimée de la base de données');
    }
}
