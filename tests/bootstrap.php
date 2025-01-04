<?php

use Symfony\Component\Dotenv\Dotenv;

// Charger l'autoloader de Composer
$autoloadFile = realpath(__DIR__ . '/../vendor/autoload.php');
if (!$autoloadFile || !file_exists($autoloadFile)) {
    throw new RuntimeException('Le fichier autoload.php est manquant.');
}
require_once $autoloadFile;

// Vérifier et charger le fichier bootstrap
$bootstrapFile = realpath(__DIR__ . '/../tests/bootstrap.php');
if ($bootstrapFile && file_exists($bootstrapFile)) {
    require_once $bootstrapFile;
} else {
    throw new RuntimeException('Le fichier bootstrap.php est manquant ou inaccessible.');
}

// Charger les variables d'environnement
$envFile = realpath(__DIR__ . '/../.env');
if ($envFile && file_exists($envFile)) {
    (new Dotenv())->bootEnv($envFile);
} else {
    throw new RuntimeException('Le fichier .env est manquant ou inaccessible.');
}
