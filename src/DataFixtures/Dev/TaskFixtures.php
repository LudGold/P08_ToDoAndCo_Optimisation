<?php

namespace App\DataFixtures\Dev;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * @codeCoverageIgnore
 */
class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager):void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer ou créer un utilisateur "anonyme"
        $anonymousUser = new User();
        $anonymousUser->setUsername('anonyme');
        $anonymousUser->setEmail('anonyme@example.com');
        $anonymousUser->setPassword(''); // Mot de passe vide ou crypté
        $manager->persist($anonymousUser);

        // Créer 10 tâches de démonstration
        for ($i = 0; $i < 10; ++$i) {
            $task = new Task();
            $task->setTitle($faker->sentence(3));
            $task->setContent($faker->paragraph());
            $task->setCreatedAt($faker->dateTimeBetween('-1 years', 'now'));
            $task->setIsDone($faker->boolean());  // Aléatoire : terminé ou non
            $task->setAuthor($anonymousUser);     // Associer l'utilisateur "anonyme"
            $manager->persist($task);
        }

        // Sauvegarde de l'utilisateur et des tâches dans la base de données
        $manager->flush();
    }
}
