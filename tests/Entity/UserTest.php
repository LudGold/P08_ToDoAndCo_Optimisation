<?php

namespace App\tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Classe de test pour l'entité User.
 */
class UserTest extends TestCase
{
    /**
     * Teste les getters et setters de l'entité User.
     */
    public function testGettersAndSetters()
    {
        $user = new User();

        $user->setUsername('testuser');
        $this->assertEquals('testuser', $user->getUsername());

        $user->setPassword('Password123!');
        $this->assertEquals('Password123!', $user->getPassword());

        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getEmail());

        $user->setRoles(['ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN'], $user->getRoles());

        $user->setResetToken('resetToken');
        $this->assertEquals('resetToken', $user->getResetToken());

        $date = new \DateTime();
        $user->setTokenExpiryDate($date);
        $this->assertEquals($date, $user->getTokenExpiryDate());
    }

    /**
     * Teste le rôle par défaut de l'entité User.
     */
    public function testDefaultRole()
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    /**
     * Teste la méthode isTokenExpired() de l'entité User.
     */
    public function testIsTokenExpired()
    {
        $user = new User();

        $this->assertTrue($user->isTokenExpired());

        $date = new \DateTime('+1 day');
        $user->setTokenExpiryDate($date);
        $this->assertFalse($user->isTokenExpired());

        $date = new \DateTime('-1 day');
        $user->setTokenExpiryDate($date);
        $this->assertTrue($user->isTokenExpired());
    }

    /**
     * Teste la validation des contraintes de l'entité User.
     */
    public function testValidation()
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword('Password123!');
        $user->setEmail('test@example.com');

        $validator  = Validation::createValidator();
        $violations = $validator->validate($user);

        $this->assertCount(0, $violations);
    }

    /**
     * Teste la méthode eraseCredentials() de l'entité User.
     */
    public function testEraseCredentials()
    {
        $user = new User();
        $this->assertNull($user->eraseCredentials());
    }

    /**
     * Teste la méthode getTasks() de l'entité User.
     */
    public function testGetTasks()
    {
        $user = new User();
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getTasks());
        $this->assertCount(0, $user->getTasks());
    }

    /**
     * Teste la méthode addTask() de l'entité User.
     */
    public function testAddTask()
    {
        $user = new User();
        $task = new Task();

        $this->assertCount(0, $user->getTasks());

        $user->addTask($task);
        $this->assertCount(1, $user->getTasks());
        $this->assertContains($task, $user->getTasks());
    }
}
