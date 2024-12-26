<?php

namespace App\tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }

    public function testLoginPage()
    {
        // Simule une requête GET vers la page de login
        $crawler = $this->client->request('GET', '/login');

        // Vérifie que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifie que le titre de la page contient "Login"
        $this->assertSelectorTextContains('form', 'Se connecter');

        // Vérifie que le champ pour le dernier nom d'utilisateur existe
        $this->assertSelectorExists('input[name="_username"]');

        // Vérifie que le champ pour le mot de passe existe
        $this->assertSelectorExists('input[name="_password"]');
    }

    
    public function testLogoutRedirection()
    {
        // Accéder à /logout
        $this->client->request('GET', '/logout');
        // Vérifiez la redirection
        $this->assertResponseRedirects();
    }
}
