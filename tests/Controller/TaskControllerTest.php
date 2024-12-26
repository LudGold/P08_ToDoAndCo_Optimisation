<?php

namespace App\Tests\Controller\Task;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\Controller\WebTestCaseBase;

class TaskControllerTest extends WebTestCaseBase
{
    public function testCreateTask(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('form[name="task"]');

        $form = $crawler->selectButton('Créer')->form([
            'task[title]' => 'Test Task',
            'task[content]' => 'Content of the test task',
        ]);
        $this->client->submit($form);

        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Test Task']);
        $this->assertNotNull($task, 'La tâche a bien été ajoutée en base de données.');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'La tâche a été ajoutée avec succès.');
    }

    public function testDeleteTask(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user@example.com']);
        $tasks = $this->entityManager->getRepository(Task::class)->findBy(['author' => $user]);

        $this->assertNotNull($user, 'L\'utilisateur existe.');
        $this->assertNotNull($tasks, 'La tâche existe.');

        $this->client->loginUser($user);
        $this->client->followRedirects();
        foreach ($tasks as $task) {
            $crawler = $this->client->request('GET', '/tasks');
            $form = $crawler->filter('form[action="/tasks/' . $task->getId() . '/delete"]')->form();
            $this->client->submit($form);

            $this->entityManager->clear();
            $deletedTask = $this->entityManager->getRepository(Task::class)->find($task->getId());
            $this->assertNull($deletedTask);
        }
    }

    public function testEditTask(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        $this->client->loginUser($user);
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['author' => $user]);
        $this->assertNotNull($task, 'Aucune tâche trouvée pour cet utilisateur.');

        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Updated Task Title',
            'task[content]' => 'Updated content',
        ]);
        $this->client->submit($form);

        $updatedTask = $this->entityManager->getRepository(Task::class)->find($task->getId());
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
}
