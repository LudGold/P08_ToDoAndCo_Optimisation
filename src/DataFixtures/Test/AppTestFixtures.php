<?php

namespace App\DataFixtures\Test;

namespace App\DataFixtures\Test;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class AppTestFixtures extends Fixture implements FixtureGroupInterface
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        // 1. Création des utilisateurs
        $users = $this->loadUsers($manager);

        // 2. Création des tâches
        $this->loadTasks($manager, $users['user']);

        // 3. Sauvegarde finale
        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): array
    {
        $users = [];

        // Utilisateur anonyme
        $anonymous = new User();
        $anonymous->setUsername('anonymous')
            ->setEmail('anonymous@example.org')
            ->setPassword($this->passwordHasher->hashPassword($anonymous, 'test'))
            ->setRoles(['ROLE_USER']);
        $manager->persist($anonymous);
        $users['anonymous'] = $anonymous;

        // Utilisateur standard
        $user = new User();
        $user->setUsername('user')
            ->setEmail('user@example.com')
            ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
            ->setRoles(['ROLE_USER']);
        $manager->persist($user);
        $users['user'] = $user;

        // Utilisateur admin
        $admin = new User();
        $admin->setUsername('admin')
            ->setEmail('admin@example.com')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'adminpassword'))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $users['admin'] = $admin;

        // Utilisateur de test avec jeton valide
        $testUserValid = new User();
        $testUserValid->setUsername('testuser1')
            ->setEmail('testuser1@example.com')
            ->setPassword($this->passwordHasher->hashPassword($testUserValid, 'password'))
            ->setRoles(['ROLE_USER'])
            ->setResetToken('valid-token')
            ->setTokenExpiryDate(new \DateTime('+1 hour'));
        $manager->persist($testUserValid);
        $users['testuser1'] = $testUserValid;

        // Utilisateur de test avec jeton invalide
        $testUserInvalid = new User();
        $testUserInvalid->setUsername('testuser2')
            ->setEmail('testuser2@example.com')
            ->setPassword($this->passwordHasher->hashPassword($testUserInvalid, 'password'))
            ->setRoles(['ROLE_USER'])
            ->setResetToken('invalid-token')
            ->setTokenExpiryDate(new \DateTime('-1 hour'));
        $manager->persist($testUserInvalid);
        $users['testuser2'] = $testUserInvalid;

        return $users;
    }

    private function loadTasks(ObjectManager $manager, User $user)
    {
        // Création de tâches pour les tests
        for ($i = 1; $i <= 5; $i++) {
            $task = new Task();
            $task->setTitle('Tâche de test ' . $i)
                ->setContent('Ceci est la description de la tâche de test ' . $i)
                ->setCreatedAt(new \DateTime())
                ->setIsDone(false)
                ->setAuthor($user);

            $manager->persist($task);
        }

        // Création de quelques tâches terminées
        for ($i = 1; $i <= 2; $i++) {
            $task = new Task();
            $task->setTitle('Tâche terminée ' . $i)
                ->setContent('Ceci est une tâche terminée ' . $i)
                ->setCreatedAt(new \DateTime())
                ->setIsDone(true)
                ->setAuthor($user);

            $manager->persist($task);
        }
    }

    public static function getGroups(): array
    {
        return ['test'];
    }
}
