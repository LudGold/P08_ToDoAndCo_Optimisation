<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $userRepository;

    protected function setUp(): void
{
    self::bootKernel();

    $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $this->userRepository = $this->entityManager->getRepository(User::class);

    // Supprimer l'utilisateur `test@test.com` s'il existe déjà
    $existingUser = $this->userRepository->findOneBy(['email' => 'test@test.com']);
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
        $this->userRepository->add($user, true);

        // Vérifier que l'utilisateur a bien été enregistré
        $savedUser = $this->userRepository->findOneBy(['email' => 'test@test.com']);
        $this->assertNotNull($savedUser);
        $this->assertEquals('testUser', $savedUser->getUsername());
        $this->assertEquals('test@test.com', $savedUser->getEmail());
    }

  
}
