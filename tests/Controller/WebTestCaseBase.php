<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Doctrine\ORM\EntityManagerInterface;

abstract class WebTestCaseBase extends WebTestCase
{
        protected $client;
        protected $entityManager;


        protected function setUp(): void
        {
                parent::setUp();
                // Créer un client HTTP
                $this->client = static::createClient();
                // Récupérer l'EntityManager
                $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
                // Charger les fixtures
                $this->loadFixtures();
        }
        private function loadFixtures(): void
        {
                $this->runCommand('doctrine:fixtures:load --env=test --quiet --no-interaction');
        }

        protected function initSession(): void
        {
                $session = $this->client->getContainer()->get('session.factory')->createSession();
                $this->client->getContainer()->set('session', $session);
                $session->start();
                $session->save();
        }
        protected function getRepository(string $class)
        {
                return $this->entityManager->getRepository($class);
        }

        protected function runCommand(string $command): void
        {
                $application = new Application(static::$kernel);
                $application->setAutoExit(false);
                $application->run(new StringInput($command));
        }

        protected function tearDown(): void
        {
                parent::tearDown();
                $this->entityManager->close();
                $this->entityManager = null;
        }
}
