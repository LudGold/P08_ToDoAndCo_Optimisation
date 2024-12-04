<?php

namespace App\tests\Controller;


use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class TaskControllerTest extends WebTestCase
{
    private $client;
    private $passwordHasher;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Charger les fixtures
        $this->loadFixtures();
    }
    private function loadFixtures(): void
    {
        // Nettoyer la base
        $this->entityManager->getConnection()->executeQuery('DELETE FROM task');
        $this->entityManager->getConnection()->executeQuery('DELETE FROM user');

        // Créer un utilisateur de test
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@test.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);

        // Créer quelques tâches de test
        $task1 = new Task();
        $task1->setTitle('Tâche de test 1');
        $task1->setContent('Contenu de test 1');
        $task1->setAuthor($user);
        $task1->setCreatedAt(new \DateTime());
        $task1->toggle(false); // tâche non terminée

        $task2 = new Task();
        $task2->setTitle('Tâche de test 2');
        $task2->setContent('Contenu de test 2');
        $task2->setAuthor($user);
        $task2->setCreatedAt(new \DateTime());
        $task2->toggle(true); // tâche terminée

        $this->entityManager->persist($task1);
        $this->entityManager->persist($task2);

        $this->entityManager->flush();
    }

    public function testListAction()
    {
        // Simule une requête GET vers la liste des tâches
        $this->client->request('GET', '/tasks');

        // Vérifie que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifie que la page contient le titre de la liste de tâches
        $this->assertSelectorTextContains('h1', 'Liste des tâches');
    }

    public function testCreateAction(): void
    {
        // Récupérer un utilisateur depuis les fixtures
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        // Simuler l'authentification de cet utilisateur
        $this->client->loginUser($user);
        // Simule une requête GET vers le formulaire de création de tâche
        $crawler = $this->client->request('GET', '/tasks/create');
        
        // Vérifie que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifie que le formulaire est présent
        $this->assertSelectorExists('form[name="task"]');

        // Simule la soumission d'un formulaire valide
        $form = $crawler->selectButton('Créer')->form([
            'task[title]' => 'Test Task',
            'task[content]' => 'Content of the test task',
        ]);
        $this->client->submit($form);
        // Vérifie que la tâche a été ajoutée en base de données
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Test Task']);
        $this->assertNotNull($task, 'La tâche a bien été ajoutée en base de données.');
        // Vérifie la redirection après soumission
        $this->assertResponseRedirects('/tasks');

        // Suivre la redirection
        $this->client->followRedirect();

        // Vérifie que la tâche a été ajoutée
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'La tâche a été ajoutée avec succès.');
    }

    public function testEditAction()
    {
        // Récupérer un utilisateur depuis les fixtures
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        // Simuler l'authentification de cet utilisateur
        $this->client->loginUser($user);
        // Suppose que vous avez une tâche à éditer dans votre base de données
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['author' => $user]);
        // S'assurer qu'une tâche a bien été trouvée
        $this->assertNotNull($task, 'Aucune tâche trouvée pour cet utilisateur.');
        // Simule une requête GET vers le formulaire d'édition de la tâche
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');

        // Vérifie que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Simule la soumission du formulaire d'édition avec des données modifiées
        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Updated Task Title',
            'task[content]' => 'Updated content',
        ]);
        $this->client->submit($form);
        // Vérifie que la tâche a bien été modifiée
        $updatedTask = $this->entityManager->getRepository(Task::class)->find($task->getId());
        $this->assertEquals('Updated Task Title', $updatedTask->getTitle());
        $this->assertEquals('Updated content', $updatedTask->getContent());

        // Vérifie la redirection après soumission
        $this->assertResponseRedirects('/tasks');

        // Suivre la redirection
        $this->client->followRedirect();

        // Vérifie que la tâche a bien été modifiée
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été modifiée.');
    }

    public function testToggleTaskAction(): void
    {
        // Récupérer un utilisateur existant (par exemple via les fixtures)
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        
        // Simuler l'authentification de cet utilisateur
        $this->client->loginUser($user);
    
        // Suppose qu'il y a une tâche existante
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['author' => $user]);
    
        // Assure-toi que la tâche n'est pas encore terminée au départ
        $this->assertFalse($task->isDone(), 'La tâche est initialement non terminée.');
    
        // Simule la requête POST pour basculer l'état d'une tâche avec un token CSRF
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('toggle' . $task->getId())->getValue();
        $this->client->request('POST', '/tasks/' . $task->getId() . '/toggle', [
            '_token' => $csrfToken
        ]);
    
        // Vérifie que la réponse est bien une redirection vers la liste des tâches
        $this->assertResponseRedirects('/tasks');
    
        // Suivre la redirection
        $crawler = $this->client->followRedirect();
    
        // Récupérer à nouveau la tâche pour vérifier son nouvel état
        $updatedTask = $this->entityManager->getRepository(Task::class)->find($task->getId());
        $this->assertTrue($updatedTask->isDone(), 'La tâche a bien été basculée en terminée.');
    
        
    }
    

//     public function testDeleteTaskAction(): void
// {
//     // Récupérer un utilisateur existant (par exemple via les fixtures)
//     $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
    
//     $this->client->loginUser($user);

//     // Récupérer une tâche existante associée à cet utilisateur
//     $task = $this->entityManager->getRepository(Task::class)->findOneBy(['author' => $user]);

//     // Vérification que la tâche existe bien avant de continuer
//     $this->assertNotNull($task, 'La tâche doit exister.');
//     $this->assertNotNull($task->getId(), 'La tâche doit avoir un identifiant.');

//     // Générer un token CSRF pour la suppression
//     $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $task->getId())->getValue();

//     // Simuler la requête POST pour supprimer la tâche
//     $this->client->request('POST', '/tasks/' . $task->getId() . '/delete', [
//         '_token' => $csrfToken
//     ]);

//     // Vérifier que la réponse est une redirection vers la liste des tâches
//     $this->assertResponseRedirects('/tasks');

//     // Suivre la redirection
//     $this->client->followRedirect();

//     // Vérifier que la tâche n'existe plus en base de données
//     $deletedTask = $this->entityManager->getRepository(Task::class)->find($task->getId());
//     $this->assertNull($deletedTask, 'La tâche a bien été supprimée de la base de données.');

//     // Vérifier que le message flash de succès est affiché
//     $this->assertSelectorExists('.alert-success');
//     $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été supprimée.');
// }

   
    public function testListDoneAction()
    {
        // Simuler la connexion d'un utilisateur, par exemple 'testuser'
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'testuser']);
        // Vérifier que l'utilisateur existe
        $this->assertNotNull($user, 'Utilisateur testuser non trouvé.');
        $this->client->loginUser($user);
        // Simule une requête GET vers la liste des tâches terminées
        $crawler = $this->client->request('GET', '/tasks/done');

        // Vérifie que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);
        // Vérifier qu'au moins une tâche terminée est affichée avec l'icône "glyphicon-ok"
        $this->assertGreaterThan(0, $crawler->filter('.glyphicon-ok')->count(), 'Aucune tâche terminée trouvée.');
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null; // évite les fuites de mémoire
    }
}
