<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        // don't seperate union types
        'binary_operator_spaces' => ['operators' => ['|' => null]],
        'phpdoc_align' => ['align' => 'left'],
        'php_unit_internal_class' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
