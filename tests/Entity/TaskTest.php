<?php

namespace App\tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testSetTitle()
    {
        $task = new Task();
        $task->setTitle('Test Task Title');

        $this->assertSame('Test Task Title', $task->getTitle());
    }

    public function testSetContent()
    {
        $task = new Task();
        $task->setContent('This is the content of the task.');

        $this->assertSame('This is the content of the task.', $task->getContent());
    }

    public function testIsDone()
    {
        $task = new Task();

        // Test par défaut que la tâche n'est pas terminée
        $this->assertFalse($task->isDone());

        // Marquer la tâche comme terminée
        $task->setIsDone(true);
        $this->assertTrue($task->isDone());

        // Marquer la tâche comme non terminée
        $task->setIsDone(false);
        $this->assertFalse($task->isDone());
    }

    public function testToggle()
    {
        $task = new Task();

        // Test de la méthode toggle pour passer de non fait à fait
        $task->toggle(true);
        $this->assertTrue($task->isDone());

        // Test de la méthode toggle pour passer de fait à non fait
        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }

    public function testSetAuthor()
    {
        $task = new Task();
        $user = new User();
        $user->setUsername('JohnDoe');

        // Associer un auteur à la tâche
        $task->setAuthor($user);

        $this->assertSame($user, $task->getAuthor());
        $this->assertSame('JohnDoe', $task->getAuthor()->getUsername());
    }

    public function testSetCreatedAt()
    {
        $task = new Task();
        $now  = new \DateTime();

        // Définir une date de création
        $task->setCreatedAt($now);

        $this->assertSame($now, $task->getCreatedAt());
    }
}
