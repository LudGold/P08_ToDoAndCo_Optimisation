<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Chargez l'autoloader
$loaderPath = __DIR__ . '/../app/autoload.php';
if (!is_file($loaderPath)) {
    throw new RuntimeException('The autoload file is missing.');
}
$loader = require $loaderPath;

// Créez un objet Request à partir des superglobales
$request = Request::createFromGlobals();

// Vérifiez les adresses IP autorisées
$allowedIps = ['127.0.0.1', '::1'];
$clientIp = $request->getClientIp();

if (
    $request->server->has('HTTP_CLIENT_IP')
    || $request->server->has('HTTP_X_FORWARDED_FOR')
    || ($clientIp && !in_array($clientIp, $allowedIps) && php_sapi_name() !== 'cli-server')
) {
    $response = new Response(
        'You are not allowed to access this file.',
        Response::HTTP_FORBIDDEN
    );
    $response->send();
    return;
}
