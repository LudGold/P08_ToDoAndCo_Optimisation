<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface; // Pour manipuler l'EntityManager
use Symfony\Component\HttpFoundation\Response;

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
        $userRepository = $entityManager->getRepository(User::class);
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
        $this->assertSelectorExists('h1', 'Modifier votre mot de passe'); // Changez selon le contenu réel du template
        $this->assertSelectorExists('form[name="reset_password"]');
    }
}
