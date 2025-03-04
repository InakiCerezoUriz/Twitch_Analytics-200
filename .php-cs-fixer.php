<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor'])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                      => true, // Aplica PSR-12
        'array_syntax'                => ['syntax' => 'short'], // Usa sintaxis corta de arrays []
        'ordered_imports'             => ['sort_algorithm' => 'alpha'], // Ordena los imports alfabéticamente
        'no_unused_imports'           => true, // Elimina imports no usados
        'trailing_comma_in_multiline' => ['elements' => ['arrays']], // Agrega comas finales en arrays multilínea
        'single_quote'                => true, // Usa comillas simples cuando sea posible
        'binary_operator_spaces'      => ['default' => 'align_single_space_minimal'], // Alinea operadores
        'no_trailing_whitespace'      => true, // Elimina espacios en blanco innecesarios
        'no_whitespace_in_blank_line' => true, // Evita líneas en blanco con espacios
    ])
    ->setFinder($finder);
