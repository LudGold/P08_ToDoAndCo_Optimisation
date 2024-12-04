<?php

use Symfony\Component\HttpFoundation\Response;

// Vérifiez les adresses IP autorisées
$allowedIps = ['127.0.0.1', '::1'];
$clientIp = $_SERVER['REMOTE_ADDR'] ?? null;

if (
    isset($_SERVER['HTTP_CLIENT_IP']) ||
    isset($_SERVER['HTTP_X_FORWARDED_FOR']) ||
    ($clientIp && !in_array($clientIp, $allowedIps) && php_sapi_name() !== 'cli-server')
) {
    $response = new Response(
        'You are not allowed to access this file.',
        Response::HTTP_FORBIDDEN
    );
    $response->send();
    return;
}

// Chargez l'autoloader
$loaderPath = __DIR__ . '/../app/autoload.php';
if (!is_file($loaderPath)) {
    throw new RuntimeException('The autoload file is missing.');
}
$loader = require $loaderPath;

// Ajoutez d'autres initialisations ici si nécessaire
