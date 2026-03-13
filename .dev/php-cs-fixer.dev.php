<?php declare(strict_types=1);

/**
 * php-cs-fixer fix -vvv --config=".dev/php-cs-fixer-core.php" .
 *
 * File name: .php-cs-fixer.php
 * Created:   2026-01-21 19:09:02
 *
 * @author    Gabriel Tenita <dev2023@tenita.eu>
 * @see       https://github.com/the-ge/
 * @copyright Copyright (c) 2024-present Gabriel Tenita
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License version 2.0
 */

require '../vendor/autoload.php';

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PhpCsFixerCustomFixers\Fixer\DeclareAfterOpeningTagFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessDirnameCallFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessParenthesisFixer;
use PhpCsFixerCustomFixers\Fixer\PromotedConstructorPropertyFixer;
use PhpCsFixerCustomFixers\Fixer\StringableInterfaceFixer;
use TheGe\PhpCsFixer\Fixer\ClassNotation\BlankLinesBeforeClassyBlockFixer;

//fwrite(STDOUT, var_export(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect(), true));

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    //->setUnsupportedPhpVersionAllowed(true)
    ->setRiskyAllowed(true)
    ->setUsingCache(false) // or
    //->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())              // composer require --dev kubawerlos/php-cs-fixer-custom-fixers
    ->registerCustomFixers([new BlankLinesBeforeClassyBlockFixer()]) // composer require --dev thege/thege-phpcsfixer-fixers
    ->setFinder((new Finder())
        ->in(__DIR__)
        ->name('*.php')
        ->name('*.inc')
        ->name('*.module')
        ->name('*.phtml')
        //->notName('*.blade.php')
        ->exclude(['.git', 'vendor'])
        ->notPath(['rector.php'])
    )
    ->setRules([ // https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/rules/index.rst
        '@PSR12'                 => true,
        '@PHP7x4Migration'       => 70400 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80000,
        '@PHP7x4Migration:risky' => 70400 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80000,
        '@PHP8x0Migration'       => 80000 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80100,
        '@PHP8x0Migration:risky' => 80000 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80100,
        '@PHP8x1Migration'       => 80100 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80200,
        '@PHP8x1Migration:risky' => 80100 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80200,
        '@PHP8x2Migration'       => 80200 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80300,
        '@PHP8x2Migration:risky' => 80200 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80300,
        '@PHP8x3Migration'       => 80300 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80400,
        '@PHP8x3Migration:risky' => 80300 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80400,
        '@PHP8x4Migration'       => 80400 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80500,
        '@PHP8x4Migration:risky' => 80400 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80500,
        '@PHP8x5Migration'       => 80500 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80600,
        '@PHP8x5Migration:risky' => 80500 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 80600,
        // ------------------------------------------------------------------------------------------ Alias
        'array_push'                       => true, // @Symfony:risky
        'ereg_to_preg'                     => true, // @Symfony:risky
        'modernize_strpos'                 => \PHP_VERSION_ID >= 80000, // [PHP 8.0+] @PHP8x0+Migration:risky
        'no_alias_language_construct_call' => true, // @Symfony
        'no_mixed_echo_print'              => ['use' => 'echo'], // @Symfony:risky
        'set_type_to_cast'                 => true, // @Symfony:risky
        // ------------------------------------------------------------------------------------------ Array Notation
        'trim_array_spaces'               => true, // @Symfony
        'whitespace_after_comma_in_array' => ['ensure_single_space' => true], // @PhpCsFixer

        // ------------------------------------------------------------------------------------------ Basic
        'braces_position' => [
            'allow_single_line_anonymous_functions'     => true, // @PSR12
            'allow_single_line_empty_anonymous_classes' => true, // @Symfony
        ],
        'no_trailing_comma_in_singleline' => [ // @Symfony
            'elements' => [
                'arguments',
                'array',
                'array_destructuring',
                'group_import',
            ],
        ],
        'single_line_empty_body' => true,
        // ------------------------------------------------------------------------------------------ Casing
        'class_reference_name_casing'    => true, // @Symfony
        'integer_literal_case'           => true, // @Symfony
        'magic_constant_casing'          => true, // @Symfony
        'magic_method_casing'            => true, // @Symfony
        'native_function_casing'         => true, // @Symfony
        'native_type_declaration_casing' => true, // @Symfony
        // ------------------------------------------------------------------------------------------ Cast Notation
        'cast_spaces'             => ['space' => 'single'], // @Symfony
        'modernize_types_casting' => true, // @Symfony:risky
        'no_short_bool_cast'      => true, // @Symfony
        'no_unset_cast'           => true, // @PHP8x0+Migration
        // ------------------------------------------------------------------------------------------ Class Notation
        'class_attributes_separation' => [
            'elements' => [
                'const'        => 'none',
                'method'       => 'one',
                'property'     => 'one',
                'trait_import' => 'none',
                'case'         => 'none',
            ],
        ],
        'class_definition' => [
            'inline_constructor_arguments'        => true,
            'multi_line_extends_each_single_line' => false,
            'single_item_single_line'             => true,
            'single_line'                         => true,
            'space_before_parenthesis'            => false,
        ],
        //'final_public_method_for_abstract_class' => true,
        'modern_serialization_methods' => true, // @PHP8x5+Migration:risky
        'no_php4_constructor'          => true, // @PHP8x0+Migration:risky
        //'no_null_property_initialization' => true, // PrestaShop will choke on this
        'no_unneeded_final_method'                 => ['private_methods' => true], // @PHP8x0+Migration:risky
        'phpdoc_readonly_class_comment_to_keyword' => \PHP_VERSION_ID >= 80200, // [PHP 8.2+] @PHP8x2+Migration:risky
        'protected_to_private'                     => true, // @Symfony
        'self_static_accessor'                     => true, // @PhpCsFixer
        'single_class_element_per_statement'       => ['elements' => ['const', 'property']], // @Symfony
        //'single_trait_insert_per_statement' => false, // @PSR12
        'stringable_for_to_string' => \PHP_VERSION_ID >= 80000, // [PHP 8.0+]
        // ------------------------------------------------------------------------------------------ Comment
        'no_empty_comment'                  => true, // @Symfony
        'multiline_comment_opening_closing' => true, // @PhpCsFixer
        'single_line_comment_spacing'       => true, // @Symfony
        'single_line_comment_style'         => ['comment_types' => ['hash']], // @Symfony

        // ------------------------------------------------------------------------------------------ Constant Notation
        'native_constant_invocation' => [ // @Symfony:risky
            'exclude'      => ['null', 'false', 'true'],
            'fix_built_in' => true,
            'include'      => [],
            'scope'        => 'all',
            'strict'       => false,
        ],
        // ------------------------------------------------------------------------------------------ Control Structure
        'empty_loop_body'                 => ['style' => 'braces'], // @Symfony
        'empty_loop_condition'            => ['style' => 'while'], // @Symfony
        'include'                         => true, // @Symfony
        'no_alternative_syntax'           => ['fix_non_monolithic_code' => true], // @Symfony
        'no_unneeded_braces'              => ['namespaces' => true], // @Symfony
        'no_unneeded_control_parentheses' => [ // @Symfony
            'statements' => [
                'break',
                'clone',
                'continue',
                'echo_print',
                'negative_instanceof',
                'others',
                'return',
                'switch_case',
                'yield',
                'yield_from',
            ],
        ],
        'no_useless_else'             => true, // @Symfony
        'simplified_if_return'        => true,
        'switch_continue_to_break'    => true, // @Symfony
        'trailing_comma_in_multiline' => [ // @Symfony
            'after_heredoc' => true,
            'elements'      => ['array_destructuring', 'arrays', 'match', 'parameters'],
        ],
        // ------------------------------------------------------------------------------------------ Function Notation
        'combine_nested_dirname' => true, // @PHP7x0+Migration:risky
        'fopen_flag_order'       => true, // @PhpCsFixer:risky
        'function_declaration'   => [
            'closure_function_spacing'   => 'none',
            'closure_fn_spacing'         => 'none',
            'trailing_comma_single_line' => false,
        ],
        'lambda_not_used_import'        => true, // @Symfony
        'multiline_promoted_properties' => \PHP_VERSION_ID >= 80000, // [PHP8.0+]
        'native_function_invocation'    => [ // @Symfony:risky
            'exclude' => [],
            'include' => ['@compiler_optimized'],
            'scope'   => 'namespaced',
            'strict'  => true,
        ],
        'nullable_type_declaration_for_default_null_value' => true, // [PHP 7.1+] @Symfony @PHP8x4+Migration
        // ------------------------------------------------------------------------------------------ Import
        'fully_qualified_strict_types' => true, // @Symfony
        'global_namespace_import'      => [ // @Symfony
            'import_classes'   => false,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'no_unneeded_import_alias' => true, // @Symfony
        'no_unused_imports'        => true, // @Symfony
        'ordered_imports'          => [
            'imports_order'  => ['const', 'function', 'class'],
            'sort_algorithm' => 'alpha',
        ],
        // ------------------------------------------------------------------------------------------ Language Construct
        'combine_consecutive_issets' => true, // @PhpCsFixer
        'combine_consecutive_unsets' => true, // @PhpCsFixer
        'declare_parentheses'        => true, // @Symfony
        'dir_constant'               => true, // @Symfony:risky
        'explicit_indirect_variable' => true, // @PhpCsFixer
        'function_to_constant'       => true, // @Symfony:risky
        'get_class_to_class_keyword' => true, // @PHP8x0+Migration:risky
        'is_null'                    => true, // @Symfony:risky
        'nullable_type_declaration'  => ['syntax' => 'question_mark'], // @Symfony

        // ------------------------------------------------------------------------------------------ Namespace Notation
        'clean_namespace'                 => true, // @PHP8x0+Migration
        'no_leading_namespace_whitespace' => true, // @Symfony
        // ------------------------------------------------------------------------------------------ Operator

        //'assign_null_coalescing_to_coalesce_equal' => true, // [PHP 7.4+] @PHP7x4+Migration
        'binary_operator_spaces' => [
            'default'   => 'single_space',
            'operators' => [
                '='  => 'align_single_space_minimal',
                '=>' => 'align_single_space_minimal',
            ],
        ],
        'concat_space'               => ['spacing' => 'none'], // @Symfony
        'logical_operators'          => true, // @Symfony:risky
        'long_to_shorthand_operator' => true, // @Symfony:risky
        'new_expression_parentheses' => \PHP_VERSION_ID >= 80000 ? ['use_parentheses' => true] : false, // [PHP 8.0+] @PHP8x4+Migration
        'new_with_parentheses'       => [ // @Symfony
            'anonymous_class' => false,
            'named_class'     => true,
        ],
        'no_useless_concat_operator'   => ['juggle_simple_strings' => true],
        'no_useless_nullsafe_operator' => true, // @Symfony
        'standardize_increment'        => true, // @Symfony
        'standardize_not_equals'       => true, // @Symfony
        //'ternary_to_null_coalescing' => true, // @PHP7x0+Migration
        'unary_operator_spaces' => ['only_dec_inc' => false], // @Symfony

        // ------------------------------------------------------------------------------------------ PHP Tag
        'blank_line_after_opening_tag' => false,
        'echo_tag_syntax'              => [ // @Symfony
            'format'                         => 'short',
            'long_function'                  => 'echo',
            'shorten_simple_statements_only' => true,
        ],
        'linebreak_after_opening_tag' => false,
        // ------------------------------------------------------------------------------------------ PHPDoc
        'align_multiline_comment' => true, // @Symfony
        'no_blank_lines_after_phpdoc' => true, // @Symfony
        'no_empty_phpdoc'                     => true, // @Symfony
        'no_superfluous_phpdoc_tags'          => ['allow_hidden_params' => true, 'remove_inheritdoc' => true], // @Symfony
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true], // @PhpCsFixer
        'phpdoc_align'                        => [
            'align'   => 'vertical',
            'spacing' => 1,
        ],
        'phpdoc_annotation_without_dot' => true, // @Symfony
        'phpdoc_indent'                 => true, // @Symfony
        'phpdoc_inline_tag_normalizer'  => [ // @Symfony
            'tags' => [
                'example',
                'id',
                'internal',
                'inheritdoc',
                'inheritdocs',
                'link',
                'source',
                'toc',
                'tutorial',
            ],
        ],
        'phpdoc_line_span' => ['const' => 'single', 'method' => null, 'property' => 'single'],
        //'phpdoc_list_type' => true, // [RISKY]
        'phpdoc_no_access'    => true, // @Symfony
        'phpdoc_no_alias_tag' => [ // @Symfony
            'replacements' => [
                'const'          => 'var',
                'link'           => 'see',
                'property-read'  => 'property',
                'property-write' => 'property',
                'type'           => 'var',
            ],
        ],
        'phpdoc_no_useless_inheritdoc' => true, // @Symfony
        'phpdoc_order'                 => ['order' => ['param', 'return', 'throws']], // @Symfony
        'phpdoc_param_order'           => true,
        'phpdoc_return_self_reference' => [ // @Symfony
            'replacements' => [
                'this'    => '$this',
                '@this'   => '$this',
                '$self'   => 'self',
                '@self'   => 'self',
                '$static' => 'static',
                '@static' => 'static',
            ],
        ],
        'phpdoc_scalar' => [ // @Symfony
            'types' => [
                'boolean',
                'callback',
                'double',
                'integer',
                'never-return',
                'never-returns',
                'no-return',
                'real',
                'str',
            ],
        ],
        'phpdoc_separation' => [
            'groups' => [
                ['Annotation', 'NamedArgumentConstructor', 'Target'],
                ['author', 'link', 'see', 'copyright', 'license', 'deprecated', 'since'],
                ['category', 'package', 'subpackage'],
                ['property', 'property-read', 'property-write'],
            ],
            'skip_unlisted_annotations' => false,
        ],
        'phpdoc_single_line_var_spacing'                => true, // @Symfony
        'phpdoc_trim_consecutive_blank_line_separation' => true, // @Symfony
        'phpdoc_trim'                                   => true, // @Symfony
        'phpdoc_types'                                  => ['groups' => ['alias', 'meta', 'simple']], // @Symfony
        'phpdoc_types_order'                            => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'], // @Symfony
        'phpdoc_var_annotation_correct_order'           => true, // @Symfony
        'phpdoc_var_without_name'                       => true, // @Symfony
        // ------------------------------------------------------------------------------------------ Return Notation
        'no_useless_return'      => true, // @Symfony
        'simplified_null_return' => true,
        // ------------------------------------------------------------------------------------------ Semicolon
        'no_empty_statement'                         => true, // @Symfony
        'no_singleline_whitespace_before_semicolons' => true, // @Symfony
        //'semicolon_after_instruction' => true, // @Symfony
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true], // @Symfony

        // ------------------------------------------------------------------------------------------ Strict
        'declare_strict_types' => ['preserve_existing_declaration' => false], // [PHP 7.0+] @PHP7x0+Migration:risky
        'strict_comparison'    => true, // @PhpCsFixer:risky
        'strict_param'         => true, // @PhpCsFixer:risky
        // ------------------------------------------------------------------------------------------ String Notation
        'explicit_string_variable'          => true, // @PhpCsFixer
        'no_binary_string'                  => true, // @Symfony
        'simple_to_complex_string_variable' => true, // @PHP8x2+Migration @Symfony
        'single_quote'                      => ['strings_containing_single_quote_chars' => false], // @Symfony

        // ------------------------------------------------------------------------------------------ Whitespace
        'array_indentation'           => true, // @Symfony
        'blank_line_before_statement' => ['statements' => ['return', 'try']],
        'no_extra_blank_lines'        => [ // @Symfony
            'tokens' => [
                'attribute',
                'break',
                'case',
                'comma',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                //'use',
                //'use_trait'
            ],
        ],
        'no_spaces_around_offset' => ['positions' => ['inside', 'outside']], // @Symfony
        'statement_indentation'   => ['stick_comment_to_next_continuous_control_statement' => true], // @Symfony
        'types_spaces'            => [ // @Symfony
            'space'                => 'none',
            'space_multiple_catch' => null,
        ],
        DeclareAfterOpeningTagFixer::name()           => true,
        NoUselessDirnameCallFixer::name()             => true,
        NoUselessParenthesisFixer::name()             => true,
        PromotedConstructorPropertyFixer::name()      => \PHP_VERSION_ID >= 80000, // [PHP 8.0+]
        StringableInterfaceFixer::name()              => \PHP_VERSION_ID >= 80000, // [PHP 8.0+]
        'TheGe/blank_lines_before_classy_block' => true,
    ])
;
