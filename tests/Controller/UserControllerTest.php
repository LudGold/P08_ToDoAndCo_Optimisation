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

        $this->client         = static::createClient();
        $this->entityManager  = static::getContainer()->get(EntityManagerInterface::class);
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
        $crawler = $this->client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="user"]');

        $form = $crawler->selectButton('Créer')->form([
            'user[username]'         => 'TestUser',
            'user[email]'            => 'test@example.com',
            'user[password][first]'  => 'Password123!',
            'user[password][second]' => 'Password123!',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // Correction du message flash attendu

        $this->assertSelectorTextContains('.alert-success', 'Votre compte a été créé avec succès');

        $user = $this->userRepository->findOneBy(['username' => 'TestUser']);
        $this->assertNotNull($user);
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
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
        // Trouver l'utilisateur
        $user = $this->userRepository->findOneBy(['username' => 'user']);
        $this->assertNotNull($user, 'L\'utilisateur "user" n\'a pas été trouvé');

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/users/' . $user->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="user"]');

        // Correction des données du formulaire
        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'UpdatedUsername', // Nouveau username
            'user[email]'    => 'newemail@example.com',
            // Ne pas inclure le mot de passe si on ne veut pas le modifier
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // Recharger l'utilisateur depuis la base
        $this->entityManager->clear(); // Important pour recharger les données
        $updatedUser = $this->userRepository->find($user->getId());

        $this->assertEquals('UpdatedUsername', $updatedUser->getUsername());
        $this->assertEquals('newemail@example.com', $updatedUser->getEmail());
    }
}
