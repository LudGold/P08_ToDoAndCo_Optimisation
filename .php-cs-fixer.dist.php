<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'node_modules']) // Exclure les dossiers inutiles
    ->name('*.php') // Cibler uniquement les fichiers PHP
    ->notName('*.blade.php'); // Exclure les fichiers spécifiques si applicable

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false) // Désactiver les fixers risqués
    ->setRules([
        '@Symfony' => true,  // Utilisation des règles Symfony
        'array_indentation' => true,  // Indentation des tableaux
        'array_syntax' => ['syntax' => 'short'], // Syntaxe courte des tableaux
        'combine_consecutive_unsets' => true,  // Combine les appels unset consécutifs
        'class_attributes_separation' => ['elements' => ['method' => 'one']], // Espacement des méthodes
        'multiline_whitespace_before_semicolons' => true, 
        'single_quote' => true,  // Utilisation des guillemets simples par défaut
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '='  => 'align_single_space_minimal',
            ],
        ],
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => true,
        'function_typehint_space' => true,
        'include' => true,
        'lowercase_cast' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_spaces_around_offset' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'object_operator_without_whitespace' => true,
        'ternary_operator_spaces' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
        'single_blank_line_at_eof' => true,  // Ligne vide à la fin des fichiers
        'phpdoc_summary' => true,  // Ajouter un point final dans les docblocks
        'blank_line_before_statement' => true,  // Ligne vide avant certaines déclarations
        'line_ending' => true,  // Forcer les fins de ligne Unix (LF)
        'no_trailing_whitespace' => true,  // Supprimer les espaces inutiles
        'no_trailing_whitespace_in_comment' => true,  // Supprimer les espaces inutiles dans les commentaires
        'single_line_comment_style' => ['comment_types' => ['hash']],  // Utiliser `//` pour les commentaires
        'no_blank_lines_after_phpdoc' => true,  // Supprimer les lignes vides après un PHPDoc
        'function_declaration' => ['closure_function_spacing' => 'none'],  // Gestion des espaces dans les déclarations de fonction
    ])
    ->setFinder($finder);

