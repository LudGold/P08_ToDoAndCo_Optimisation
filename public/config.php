<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Chargez les variables d'environnement
$autoloadPath = realpath(__DIR__ . '/../vendor/autoload.php');
if ($autoloadPath && is_readable($autoloadPath)) {
    require_once $autoloadPath;
} else {
    throw new RuntimeException('Autoload file not found or not readable');
}

$dotenv = new Dotenv();
$dotenv->bootEnv(__DIR__ . '/../.env');

// Créez une requête à partir des superglobales
$request = Request::createFromGlobals();

// Vérifiez que le script n'est pas exécuté depuis le CLI
if (!$request->server->has('HTTP_HOST')) {
    $response = new Response(
        'This script cannot be run from the CLI. Run it from a browser.',
        Response::HTTP_FORBIDDEN
    );
    $response->send();

    return;
}

// Vérifiez l'adresse IP de l'utilisateur
$allowedIps = ['127.0.0.1', '::1'];
$clientIp   = $request->getClientIp();
if ($clientIp && !in_array($clientIp, $allowedIps)) {
    $response = new Response(
        'This script is only accessible from localhost.',
        Response::HTTP_FORBIDDEN
    );
    $response->send();

    return;
}

// Chargez les exigences Symfony
$requirementsPath = __DIR__ . '/../var/SymfonyRequirements.php';
if (is_readable($requirementsPath)) {
    require_once realpath($requirementsPath);
} else {
    $response = new Response(
        'Symfony requirements file is missing.',
        Response::HTTP_INTERNAL_SERVER_ERROR
    );
    $response->send();

    return;
}

// Préparez les problèmes (exemple simulé ici, remplacez avec votre logique réelle)
$problems = [
    (object) [
        'getTestMessage' => fn () => 'Test message 1',
        'getHelpHtml'    => fn () => 'Help message 1',
    ],
    (object) [
        'getTestMessage' => fn () => 'Test message 2',
        'getHelpHtml'    => fn () => 'Help message 2',
    ],
];

// Créez une réponse HTML sécurisée
$responseContent = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration</title>
</head>
<body>
    <h1>Symfony Configuration Check</h1>
    <ul>';
foreach ($problems as $problem) {
    $responseContent .= sprintf(
        '<li>%s</li><p class="help"><em>%s</em></p>',
        htmlspecialchars($problem->getTestMessage(), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($problem->getHelpHtml(), ENT_QUOTES, 'UTF-8')
    );
}
$responseContent .= '
    </ul>
</body>
</html>';

// Envoyez la réponse finale
$response = new Response($responseContent);
$response->send();
