<?php

$preloadFile = __DIR__.'/../var/cache/prod/App_KernelProdContainer.preload.php';

// Vérifiez si le fichier existe avant de le charger.
if (file_exists($preloadFile) && is_readable($preloadFile)) {
    require $preloadFile;
} else {
    // Optionnel : Ajoutez une gestion des erreurs ou un message explicite si nécessaire.
    throw new RuntimeException('Le fichier App_KernelProdContainer.preload.php est introuvable.');
}
