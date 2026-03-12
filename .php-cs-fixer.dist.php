<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use TheGe\PhpCsFixer\Fixer\ClassNotation\ClassyDeclarationAfterTwoBlankLinesFixer;

require_once __DIR__ . '/vendor/autoload.php';

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return (new Config())
    ->setRules([
        '@PSR12'                  => true,
        '@PHP80Migration'         => true,
        'declare_strict_types'    => true,
        'ordered_imports'         => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'       => true,
        'TheGe/classy_declaration_after_two_blank_lines' => true,
    ])
    ->registerCustomFixers([
        new ClassyDeclarationAfterTwoBlankLinesFixer(),
    ])
    ->setFinder($finder);
