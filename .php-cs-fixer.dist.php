<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'node_modules']) // Exclure les dossiers inutiles
    ->name('*.php') // Cibler uniquement les fichiers PHP
    ->notName('*.blade.php'); // Exclure des fichiers spécifiques si applicable

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false) // Désactiver les fixers risqués
    ->setRules([
        '@Symfony' => true, // Règles PSR-12 et Symfony
        'phpdoc_summary' => true, // Ajouter un point final dans les résumés de docblocks
        'single_blank_line_at_eof' => true, // Ajouter une ligne vide à la fin des fichiers
        'blank_line_before_statement' => true, // Ajouter une ligne vide avant certaines déclarations
        'indentation' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'], // Syntaxe courte des tableaux
        'line_ending' => true, // Forcer l'utilisation des fins de ligne Unix LF
        'no_trailing_whitespace' => true, // Supprimer les espaces inutiles
        'no_trailing_whitespace_in_comment' => true, // Supprimer les espaces inutiles dans les commentaires
        'single_line_comment_style' => ['comment_types' => ['hash']], // Utiliser `//` pour les commentaires
        'no_blank_lines_after_phpdoc' => true, // Supprimer les lignes vides après un PHPDoc
        'function_declaration' => ['closure_function_spacing' => 'none'], // Gérer les espaces dans les fonctions
    ])
    ->setFinder($finder);
