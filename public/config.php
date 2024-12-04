<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Dotenv\Dotenv;

// Vérifiez que le script n'est pas exécuté depuis le CLI
if (!isset($_SERVER['HTTP_HOST'])) {
    $response = new Response('This script cannot be run from the CLI. Run it from a browser.', Response::HTTP_FORBIDDEN);
    $response->send();
    exit;
}

// Vérifiez l'adresse IP
$allowedIps = ['127.0.0.1', '::1'];
if (isset($_SERVER['REMOTE_ADDR']) && !in_array($_SERVER['REMOTE_ADDR'], $allowedIps)) {
    $response = new Response('This script is only accessible from localhost.', Response::HTTP_FORBIDDEN);
    $response->send();
    exit;
}

// Chargez les exigences Symfony
$requirementsFile = __DIR__ . '/../var/SymfonyRequirements.php';
if (is_file($requirementsFile)) {
    require_once $requirementsFile;
}

// Affichage sécurisé
?>
<!DOCTYPE html>
<html>
<head>
    <title>Configuration</title>
</head>
<body>
    <ul>
        <?php foreach ($problems as $problem): ?>
            <li><?php echo htmlspecialchars($problem->getTestMessage(), ENT_QUOTES, 'UTF-8'); ?></li>
            <p class="help"><em><?php echo htmlspecialchars($problem->getHelpHtml(), ENT_QUOTES, 'UTF-8'); ?></em></p>
        <?php endforeach; ?>
    </ul>
</body>
</html>
