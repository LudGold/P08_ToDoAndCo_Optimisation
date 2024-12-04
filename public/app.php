<?php

$autoloadFile = __DIR__ . '/../app/autoload.php';
$bootstrapFile = __DIR__ . '/../var/bootstrap.php.cache';

// Vérifiez que le fichier autoload existe
if (!is_file($autoloadFile)) {
    throw new RuntimeException('Le fichier autoload.php est manquant.');
}
$loader = require $autoloadFile;

// Vérifiez que le fichier bootstrap existe
if (!is_file($bootstrapFile)) {
    throw new RuntimeException('Le fichier bootstrap.php.cache est manquant.');
}
include_once $bootstrapFile;

// Ajoutez d'autres initialisations ici si nécessaire
