<?php

namespace App\tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\DataFixtures\Test\AppTestFixtures;
use App\Repository\UserRepository;
use Symfony\Component\BrowserKit\Cookie as BrowserKitCookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
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
    public function testListActionForAdmin()
    { // Récupérer un utilisateur administrateur (supposons que les fixtures aient ajouté un admin)
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->assertNotNull($user, 'Un utilisateur admin doit exister dans la base de données.');

        $this->client->loginUser($user);

        // Simuler une requête GET vers la liste des utilisateurs
        $crawler = $this->client->request('GET', '/admin/users');

        // // Vérifier que la table d'utilisateurs existe
        $this->assertSelectorExists('table.table');

        // Vérifier qu'il y a bien des utilisateurs dans la table
        $this->assertGreaterThan(0, $crawler->filter('tbody tr')->count(), 'La table doit contenir des utilisateurs.');
    }

    // public function testEditOwnProfile()
    // {
    //     // Récupérer un utilisateur standard
    //     $user = $this->userRepository->findOneBy(['username' => 'user']);
    //     $this->assertNotNull($user, 'User should exist in the database.');

    //     // Simuler une connexion en tant qu'utilisateur standard
    //     $this->client->loginUser($user);

    //     // Simuler une requête GET vers la page d'édition de son propre profil
    //     $crawler = $this->client->request('GET', '/users/' . $user->getId() . '/edit');

    //     // Vérifier que la page d'édition renvoie un code 200
    //     $this->assertResponseStatusCodeSame(200);

    //     // Remplir le formulaire avec des modifications
    //     $form = $crawler->selectButton('Modifier')->form([
    //         'user[username]' => 'updatedUsername',
    //         'user[email]' => 'updateduser@example.com',
    //         'user[password][first]' => 'newpassword',
    //         'user[password][second]' => 'newpassword',
    //     ]);

    //     // Soumettre le formulaire
    //     $this->client->submit($form);

    //     // Vérifier la redirection après la modification
    //     $this->assertResponseRedirects('/');

    //     // Suivre la redirection
    //     $this->client->followRedirect();

    //     // Vérifier qu'un message flash de succès apparaît
    //     $this->assertSelectorTextContains('.flash-success', 'Votre profil a été mis à jour.');

    //     // Vérifier que les modifications ont été appliquées dans la base de données
    //     $updatedUser = $this->userRepository->findOneBy(['username' => 'updatedUsername']);
    //     $this->assertNotNull($updatedUser, 'Updated user should exist in the database.');
    //     $this->assertEquals('updateduser@example.com', $updatedUser->getEmail());
    // }
    // public function testAdminEditOtherUserRoles()
    // {
    //     // Récupérer un administrateur
    //     $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
    //     $this->assertNotNull($adminUser);

    //     // Simuler une connexion en tant qu'admin
    //     $this->client->loginUser($adminUser);

    //     // Récupérer un autre utilisateur à éditer
    //     $user = $this->userRepository->findOneBy(['username' => 'user']);
    //     $this->assertNotNull($user);

    //     // Simuler une requête GET vers la page d'édition de cet utilisateur
    //     $crawler = $this->client->request('GET', '/users/' . $user->getId() . '/edit');

    //     // Vérifier que la page d'édition est accessible
    //     $this->assertResponseStatusCodeSame(200);

    //     // Modifier les rôles de l'utilisateur
    //     $form = $crawler->selectButton('Modifier')->form([
    //         'user[roles]' => [], // Modification du rôle de l'utilisateur
    //     ]);

    //     // Soumettre le formulaire
    //     $this->client->submit($form);

    //     // Vérifier la redirection après la modification
    //     $this->assertResponseRedirects('admin_user_list');

    //     // Suivre la redirection et vérifier le message de succès
    //     $this->client->followRedirect();
    //     $this->assertSelectorTextContains('.flash-success', 'Votre profil a été mis à jour.');

    //     // Vérifier que les modifications sont bien dans la base de données
    //     $updatedUser = $this->userRepository->findOneBy(['username' => 'user']);
    //     $this->assertContains('ROLE_ADMIN', $updatedUser->getRoles());
    // }
    // public function testAdminEditOwnRoles()
    // {
    //     // Récupérer un administrateur
    //     $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
    //     $this->assertNotNull($adminUser);

    //     // Simuler une connexion en tant qu'admin
    //     $this->client->loginUser($adminUser);

    //     // Simuler une requête GET vers la page d'édition de son propre profil
    //     $crawler = $this->client->request('GET', '/users/' . $adminUser->getId() . '/edit');

    //     // Vérifier que la page d'édition est accessible
    //     $this->assertResponseStatusCodeSame(200);

    //     // Remplir le formulaire et modifier son rôle
    //     $form = $crawler->selectButton('Modifier')->form([
    //         'user[username]' => 'admin',
    //         'user[roles]' => ['ROLE_ADMIN', 'ROLE_USER'], // Modification de ses propres rôles
    //     ]);

    //     // Soumettre le formulaire
    //     $this->client->submit($form);

    //     // Vérifier la redirection après la modification
    //     $this->assertResponseRedirects('/');

    //     // Suivre la redirection et vérifier le message de succès
    //     $this->client->followRedirect();
    //     $this->assertSelectorTextContains('.flash-success', 'Votre profil a été mis à jour.');

    //     // Vérifier que les modifications sont bien dans la base de données
    //     $updatedAdmin = $this->userRepository->findOneBy(['username' => 'admin']);
    //     $this->assertContains('ROLE_ADMIN', $updatedAdmin->getRoles());
    // }

}
