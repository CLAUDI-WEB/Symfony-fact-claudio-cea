<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->name('*.php')
    ->exclude([
        'vendor',
        'var',
        'node_modules',
        'public/bundles',
    ])
    ->notPath('bundles.php')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => false,
        'phpdoc_to_comment' => false,
        'yoda_style' => false,
        'concat_space' => ['spacing' => 'one'],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
;
