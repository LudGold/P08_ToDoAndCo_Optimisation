<?php

namespace App\tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private $client;

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
