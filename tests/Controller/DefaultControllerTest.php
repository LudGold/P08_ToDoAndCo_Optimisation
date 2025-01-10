<?php

namespace App\tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Classe de test pour le contrôleur par défaut.
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\BrowserKit\AbstractBrowser Client HTTP de test.
     */
    private $client;

    /**
     * Teste l'accès à la page d'accueil pour un utilisateur anonyme.
     * 
     * @return void
     */
    public function testIndexPageForAnonymousUser(): void
    {
        // Crée un client de test
        $this->client = static::createClient();

        // Simule une requête GET vers la page d'accueil
        $this->client->request('GET', '/');

        // Vérifie que la réponse HTTP est 200 OK
        $this->assertResponseStatusCodeSame(200);
    }
}