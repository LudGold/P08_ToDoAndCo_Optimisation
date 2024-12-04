<?php

use Symfony\Component\Dotenv\Dotenv;

// Charger l'autoloader de Composer
$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoloadFile)) {
    throw new RuntimeException('Le fichier autoload.php est manquant.');
}
require $autoloadFile;

// Vérifier et charger le fichier bootstrap
$bootstrapFile = __DIR__ . '/../config/bootstrap.php';
if (is_file($bootstrapFile)) {
    require $bootstrapFile;
}

// Charger les variables d'environnement
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    (new Dotenv())->bootEnv($envFile);
} else {
    throw new RuntimeException('Le fichier .env est manquant.');
}
