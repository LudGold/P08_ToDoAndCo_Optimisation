<?php

namespace App\tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Classe de test pour le contrôleur de réinitialisation de mot de passe.
 */
class PasswordResetControllerTest extends WebTestCase
{
    /**
     * Teste l'accessibilité de la route de demande de réinitialisation.
     * 
     * @return void
     */
    public function testForgotPasswordRouteIsAccessible(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/forgot-password');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="password_reset_request"]');
    }

    /**
     * Teste l'accès à la page de réinitialisation avec un token valide.
     * 
     * @return void
     */
    public function testResetPasswordRouteWithValidToken(): void
    {
        $client = static::createClient();
        $entityManager  = static::getContainer()->get(EntityManagerInterface::class);
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('testuser@example.com');
        $user->setPassword('password123');
        $user->setResetToken('valid-token');
        $user->setTokenExpiryDate(new \DateTime('+1 hour'));
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $client->request('GET', '/reset-password/valid-token');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="reset_password"]');
    }

    /**
     * Teste l'accès à la page de réinitialisation avec un token invalide.
     * 
     * @return void
     */
    public function testResetPasswordRouteWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password/invalid-token');
        $this->assertResponseRedirects('/forgot-password');
    }

    /**
     * Teste le rendu du template de la page de demande de réinitialisation.
     * 
     * @return void
     */
    public function testForgotPasswordTemplateIsRendered(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/forgot-password');
        $this->assertSelectorExists('h1', 'Réinitialiser votre mot de passe');
        $this->assertSelectorExists('form[name="password_reset_request"]');
    }

    /**
     * Teste le rendu du template de la page de réinitialisation avec un token valide.
     * 
     * @return void
     */
    public function testResetPasswordTemplateIsRendered(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('testuser@example.com');
        $user->setPassword('password123');
        $user->setResetToken('valid-token');
        $user->setTokenExpiryDate(new \DateTime('+1 hour'));
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $client->request('GET', '/reset-password/valid-token');
        $this->assertSelectorTextContains('button', 'Réinitialiser mon mot de passe');
        $this->assertSelectorExists('form[name="reset_password"]');
    }
}
