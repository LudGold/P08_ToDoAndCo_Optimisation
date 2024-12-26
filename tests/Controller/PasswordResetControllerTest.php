<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase; // Pour manipuler l'EntityManager

class PasswordResetControllerTest extends WebTestCase
{

    public function testForgotPasswordRouteIsAccessible()
    {
        $client = static::createClient();

        // Accéder à la route /forgot-password
        $crawler = $client->request('GET', '/forgot-password');

        // Vérifier que la réponse est correcte
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="password_reset_request"]');
    }

    public function testResetPasswordRouteWithValidToken()
    {
        $client = static::createClient();

        // Récupérer les services nécessaires
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $email = 'testuser_' . uniqid() . '@example.com';
        // Créer un utilisateur avec un token valide
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail($email);
        $user->setPassword('SecurePassword123!');
        $user->setResetToken('valid-token');
        $user->setTokenExpiryDate(new \DateTime('+1 hour')); // Token valide pendant 1 heure
        $entityManager->persist($user);
        $entityManager->flush();

        // Accéder à la route avec le token valide
        $crawler = $client->request('GET', '/reset-password/valid-token');

        // Vérifier que la réponse est correcte
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="reset_password"]');
    }

    public function testResetPasswordRouteWithInvalidToken()
    {
        $client = static::createClient();

        // Simuler un token invalide
        $client->request('GET', '/reset-password/invalid-token');

        // Vérifier que l'utilisateur est redirigé vers la page de demande
        $this->assertResponseRedirects('/forgot-password');
    }

    public function testForgotPasswordTemplateIsRendered()
    {
        $client = static::createClient();

        // Accéder à la route /forgot-password
        $crawler = $client->request('GET', '/forgot-password');

        // Vérifier que le bon template est utilisé
        $this->assertSelectorExists('h1', 'Réinitialiser votre mot de passe'); // Changez selon le contenu de votre template
        $this->assertSelectorExists('form[name="password_reset_request"]');
    }

    public function testResetPasswordTemplateIsRendered()
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $email = 'testuser_' . uniqid() . '@example.com';

        // Créer un utilisateur avec un token valide
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail($email);
        $user->setPassword('SecurePassword123!');
        $user->setResetToken('valid-token');
        $user->setTokenExpiryDate(new \DateTime('+1 hour')); // Token valide pendant 1 heure
        $entityManager->persist($user);
        $entityManager->flush();

        // Accéder à la route avec le token valide
        $crawler = $client->request('GET', '/reset-password/valid-token');

        // Vérifier que le bon template est affiché
        $this->assertSelectorTextContains('button', 'Réinitialiser mon mot de passe');
        //Changez selon le contenu réel du template
        $this->assertSelectorExists('form[name="reset_password"]');
    }
    // test pour un formulaire invalide 
    public function testForgotPasswordWithInvalidForm()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/forgot-password');

        $form = $crawler->selectButton('Envoyer')->form([
            'password_reset_request[username]' => '', // Champ vide
        ]);
        $client->submit($form);


        $this->assertResponseIsSuccessful();
    }
    public function testResetPasswordWithExpiredToken()
    {
        $client = static::createClient();

        // Créer un utilisateur avec un token expiré
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $email = 'expired_' . uniqid() . '@example.com'; // Email unique

        $user = new User();
        $user->setUsername('expireduser_' . uniqid()); // Username unique aussi
        $user->setEmail($email); // Utiliser l'email unique
        $user->setPassword('Password123!');
        $user->setResetToken('expired-token');
        $user->setTokenExpiryDate(new \DateTime('-1 hour')); // Token expiré

        $entityManager->persist($user);
        $entityManager->flush();

        $client->request('GET', '/reset-password/expired-token');

        // Vérifiez la redirection avec un message d'erreur
        $this->assertResponseRedirects('/forgot-password');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Le jeton de réinitialisation est invalide ou expiré.');
    }
    public function testResetPasswordWithValidForm(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    
        // Créer un utilisateur test avec des identifiants uniques
        $uniqueEmail = 'test_' . uniqid() . '@example.com';
        $user = new User();
        $user->setUsername('testuser_' . uniqid());
        $user->setEmail($uniqueEmail);
        
        // Définir un token de réinitialisation valide
        $validToken = 'valid-token-' . uniqid();
        $user->setResetToken($validToken);
        $user->setTokenExpiryDate(new \DateTime('+1 hour'));
        
        // Définir le mot de passe initial
        $user->setPassword($passwordHasher->hashPassword($user, 'OldPassword123!'));
        
        $entityManager->persist($user);
        $entityManager->flush();
        
        // Sauvegarder le mot de passe initial
        $initialPassword = $user->getPassword();
    
        // Faire la requête de réinitialisation
        $crawler = $client->request('GET', '/reset-password/' . $validToken);
        
        // Soumettre le nouveau mot de passe
        $form = $crawler->selectButton('Réinitialiser mon mot de passe')->form([
            'reset_password[plainPassword]' => 'NewSecurePassword123!'
        ]);
        
        $client->submit($form);
    
        // Vérifier la redirection vers login
        $this->assertResponseRedirects('/login');
    
        // Important : vider l'EntityManager pour forcer le rechargement depuis la base
        $entityManager->clear();
        
        // Recharger l'utilisateur
        $updatedUser = $entityManager->getRepository(User::class)->find($user->getId());
        
        // Verifier que le token a été réinitialisé
        $this->assertNull($updatedUser->getResetToken());
        
        // Vérifier que le nouveau mot de passe fonctionne
        $this->assertTrue($passwordHasher->isPasswordValid(
            $updatedUser,
            'NewSecurePassword123!'
        ));
    }
}
