<?php

use Symfony\Component\Filesystem\Path;

// Utilisation sécurisée de __DIR__
$autoloadRuntimeFile = Path::canonicalize(__DIR__.'/../vendor/autoload_runtime.php');

if (!file_exists($autoloadRuntimeFile)) {
    throw new RuntimeException('Le fichier autoload_runtime.php est manquant.');
}

require_once $autoloadRuntimeFile;
