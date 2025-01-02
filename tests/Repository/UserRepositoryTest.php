<?php

namespace App\tests\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->entityManager->getRepository(User::class);

        // Supprimer l'utilisateur `test@test.com` s'il existe déjà
        $existingUser = $this->repository->findOneBy(['email' => 'test@test.com']);
        if ($existingUser) {
            $this->entityManager->remove($existingUser);
            $this->entityManager->flush();
        }
    }

    public function testAddUser()
    {
        // Création de l'utilisateur de test
        $user = new User();
        $user->setUsername('testUser');
        $user->setPassword('password123');
        $user->setEmail('test@test.com');

        // Ajouter l'utilisateur et sauvegarder
        $this->repository->add($user, true);

        // Vérifier que l'utilisateur a bien été enregistré
        $savedUser = $this->repository->findOneBy(['email' => 'test@test.com']);
        $this->assertNotNull($savedUser);
        $this->assertEquals('testUser', $savedUser->getUsername());
        $this->assertEquals('test@test.com', $savedUser->getEmail());
    }

    public function testRemove()
    {
        // Création de l'utilisateur de test
        $user = new User();
        $user->setUsername('testUser');
        $user->setPassword('password123');
        $user->setEmail('test@test.com');

        // Ajouter l'utilisateur et sauvegarder
        $this->repository->add($user, true);

        // Vérifier que l'utilisateur a bien été enregistré
        $savedUser = $this->repository->findOneBy(['email' => 'test@test.com']);
        $this->assertNotNull($savedUser);

        // Supprimer l'utilisateur
        $this->repository->remove($savedUser, true);

        // Vérifier que l'utilisateur a bien été supprimé
        $removedUser = $this->repository->findOneBy(['email' => 'test@test.com']);
        $this->assertNull($removedUser);
    }

    public function testRemoveWithoutFlush()
    {
        // Création de l'utilisateur de test
        $user = new User();
        $user->setUsername('testUser');
        $user->setPassword('password123');
        $user->setEmail('test@test.com');

        // Ajouter l'utilisateur et sauvegarder
        $this->repository->add($user, true);

        // Vérifier que l'utilisateur a bien été enregistré
        $savedUser = $this->repository->findOneBy(['email' => 'test@test.com']);
        $this->assertNotNull($savedUser);

        // Supprimer l'utilisateur sans flush
        $this->repository->remove($savedUser, false);

        // Vérifier que l'utilisateur n'a pas été supprimé de la base de données
        $removedUser = $this->repository->findOneBy(['email' => 'test@test.com']);
        $this->assertNotNull($removedUser);

        // Forcer le flush pour vérifier que l'utilisateur est bien supprimé
        $this->entityManager->flush();
        $removedUser = $this->repository->findOneBy(['email' => 'test@test.com']);
        $this->assertNull($removedUser);
    }
}
