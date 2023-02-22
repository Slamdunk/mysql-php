<?php

declare(strict_types=1);

$config = (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'error_suppression'=> false,
        'php_unit_test_case_static_method_calls' => false,
    ]);

$config->getFinder()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

return $config;
