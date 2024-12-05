<?php

namespace App\tests\Controller;

use App\DataFixtures\Test\AppTestFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);

        // Récupérer le service UserPasswordHasherInterface depuis le conteneur
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Charger les fixtures avant chaque test
        $this->loadFixtures($passwordHasher);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUpDatabase();
        $this->entityManager->close();
        $this->entityManager = null; // Évite les fuites de mémoire
    }

    private function cleanUpDatabase(): void
    {
        // Supprimer les utilisateurs et les tâches associées
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $this->entityManager->getConnection()->executeStatement('TRUNCATE TABLE user');
        $this->entityManager->getConnection()->executeStatement('TRUNCATE TABLE task');
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function loadFixtures(UserPasswordHasherInterface $passwordHasher): void
    {
        // Nettoyer la base
        $this->entityManager->getConnection()->executeQuery('DELETE FROM task');
        $this->entityManager->getConnection()->executeQuery('DELETE FROM user');

        // Charger les nouvelles fixtures avec le service injecté
        $fixtures = new AppTestFixtures($passwordHasher);
        $fixtures->load($this->entityManager);
    }

    public function testCreateUser(): void
    {
        // Simuler une requête GET pour accéder au formulaire de création
        $crawler = $this->client->request('GET', '/users/create');

        // Vérifier que le formulaire est bien accessible
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="user"]');

        // Soumettre le formulaire avec des données valides
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'TestUser',
            'user[email]' => 'test@example.com',
            'user[password][first]' => 'Password123!',
            'user[password][second]' => 'Password123!',
        ]);
        $this->client->submit($form);

        // Vérifier que la redirection est correcte
        $this->assertResponseRedirects('/'); // Mettez ici la bonne route.

        // Suivre la redirection
        $this->client->followRedirect();

        // Vérifier que l'utilisateur a été créé avec succès
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Vous avez bien été ajouté.');

        // Vérifiez que l'utilisateur est bien dans la base de données
        $user = $this->userRepository->findOneBy(['username' => 'TestUser']);
        $this->assertNotNull($user);
        $this->assertEquals(['ROLE_USER'], $user->getRoles(), 'The user should have the ROLE_USER role');
    }

    public function testListActionForAdmin(): void
    {
        // Récupérer un utilisateur administrateur (supposons que les fixtures aient ajouté un admin)
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->assertNotNull($user, 'Un utilisateur admin doit exister dans la base de données.');

        $this->client->loginUser($user);

        // Simuler une requête GET vers la liste des utilisateurs
        $crawler = $this->client->request('GET', '/admin/users');

        // Vérifier que la table d'utilisateurs existe
        $this->assertSelectorExists('table.table');

        // Vérifier qu'il y a bien des utilisateurs dans la table
        $this->assertGreaterThan(0, $crawler->filter('tbody tr')->count(), 'La table doit contenir des utilisateurs.');
    }

    public function testUserCanEditOwnProfile(): void
    {
        // Récupérer un utilisateur standard depuis les fixtures
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->assertNotNull($user, 'Un utilisateur de test doit exister dans la base de données.');

        // Connecter l'utilisateur
        $this->client->loginUser($user);

        // Simuler une requête GET vers la page d'édition de son propre profil
        $crawler = $this->client->request('GET', '/users/'.$user->getId().'/edit');

        // Vérifier que la page d'édition est accessible
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="user"]');

        // Soumettre le formulaire avec de nouvelles données
        $form = $crawler->selectButton('Modifier mon profil')->form([
            'user[email]' => 'newemail@example.com',
            'user[password][first]' => 'NewPassword123!',
            'user[password][second]' => 'NewPassword123!',
        ]);

        $this->client->submit($form);

        // Vérifier la redirection après la modification
        $this->assertResponseRedirects('/');

        // Suivre la redirection et vérifier le message de succès
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Votre profil a été mis à jour.');

        // Vérifier que les modifications sont bien dans la base de données
        $updatedUser = $this->userRepository->findOneBy(['email' => 'newemail@example.com']);
        $this->assertNotNull($updatedUser);
        $this->assertEquals($user->getId(), $updatedUser->getId());
    }
}
