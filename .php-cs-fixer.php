<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new Config())
    ->setRules([
        '@PSR12'                 => true,
        'array_syntax'           => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],
        'blank_line_after_namespace'   => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement'  => [
            'statements' => ['return'],
        ],
        'braces' => [
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        'cast_spaces'                 => ['space' => 'single'],
        'class_attributes_separation' => [
            'elements' => ['method' => 'one'],
        ],
        'concat_space'                          => ['spacing' => 'one'],
        'declare_equal_normalize'               => ['space' => 'single'],
        'function_declaration'                  => ['closure_function_spacing' => 'one'],
        'indentation_type'                      => true,
        'line_ending'                           => true,
        'lowercase_keywords'                    => true,
        'method_argument_space'                 => ['on_multiline' => 'ensure_fully_multiline'],
        'no_closing_tag'                        => true,
        'no_extra_blank_lines'                  => true,
        'no_leading_import_slash'               => true,
        'no_leading_namespace_whitespace'       => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unused_imports'                     => true,
        'ordered_imports'                       => ['sort_algorithm' => 'alpha'],
        'phpdoc_align'                          => true,
        'phpdoc_indent'                         => true,
        'phpdoc_no_access'                      => true,
        'phpdoc_no_empty_return'                => true,
        'phpdoc_no_package'                     => true,
        'phpdoc_scalar'                         => true,
        'phpdoc_single_line_var_spacing'        => true,
        'phpdoc_summary'                        => true,
        'phpdoc_to_comment'                     => true,
        'phpdoc_trim'                           => true,
        'phpdoc_types'                          => true,
        'phpdoc_var_without_name'               => true,
        'single_blank_line_at_eof'              => true,
        'single_import_per_statement'           => true,
        'single_line_after_imports'             => true,
        'single_quote'                          => true,
        'space_after_semicolon'                 => true,
        'standardize_not_equals'                => true,
        'ternary_operator_spaces'               => true,
        'trailing_comma_in_multiline'           => true,
        'trim_array_spaces'                     => true,
        'unary_operator_spaces'                 => true,
        'whitespace_after_comma_in_array'       => true,
    ])
    ->setFinder($finder);
