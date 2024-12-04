<?php

namespace App\tests\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DataFixtureTestCase extends WebTestCase
{
    /** @var Application $application */
    protected static $application;

    /** @var KernelBrowser $client */
    protected $client;
    
    /** @var ContainerInterface $containerTest */
    protected $containerTest;

    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // Exécution des commandes pour préparer la base de données
        self::runCommand('doctrine:database:drop --force --env=test');
        self::runCommand('doctrine:database:create --env=test');
        self::runCommand('doctrine:schema:create --env=test');
        self::runCommand('doctrine:fixtures:load --no-interaction --env=test --group=test');

        // Création d'un client de test et récupération du container
        $this->client = static::createClient();
        $this->containerTest = $this->client->getContainer();
        $this->entityManager = $this->containerTest->get('doctrine.orm.entity_manager');
    }

    /**
     * Exécuter une commande Symfony
     */
    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);
        return self::getApplication()->run(new StringInput($command));
    }

    /**
     * Obtenir l'application Symfony
     */
    protected static function getApplication()
    {
        if (null === self::$application) {
            $kernel = static::createKernel();
            self::$application = new Application($kernel);
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        // Supprimer la base de données après chaque test
        self::runCommand('doctrine:database:drop --force --env=test');

        // Fermeture de l'EntityManager pour éviter les fuites de mémoire
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}
