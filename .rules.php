<?php

/**
 * This file is part of Liaison Installer for CodeIgniter4.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    '@PSR2'                                            => true, // switch to PSR12 when available
    'array_syntax'                                     => ['syntax' => 'short'],
    'backtick_to_shell_exec'                           => true,
    'binary_operator_spaces'                           => ['default' => 'align_single_space'],
    'blank_line_after_opening_tag'                     => true,
    'cast_spaces'                                      => true,
    'combine_consecutive_unsets'                       => true,
    'compact_nullable_typehint'                        => true,
    'concat_space'                                     => ['spacing' => 'one'],
    'explicit_indirect_variable'                       => true,
    'function_to_constant'                             => ['functions' => ['get_called_class', 'get_class', 'get_class_this', 'php_sapi_name', 'phpversion', 'pi']],
    'function_typehint_space'                          => true,
    'include'                                          => true,
    'increment_style'                                  => ['style' => 'post'],
    'linebreak_after_opening_tag'                      => true,
    'list_syntax'                                      => ['syntax' => 'short'],
    'logical_operators'                                => true,
    'lowercase_cast'                                   => true,
    'lowercase_static_reference'                       => true,
    'mb_str_functions'                                 => true,
    'method_chaining_indentation'                      => true,
    'modernize_types_casting'                          => true,
    'multiline_comment_opening_closing'                => true,
    'multiline_whitespace_before_semicolons'           => ['strategy' => 'new_line_for_chained_calls'],
    'native_function_invocation'                       => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced', 'strict' => true],
    'new_with_braces'                                  => true,
    'no_blank_lines_after_class_opening'               => true,
    'no_blank_lines_after_phpdoc'                      => true,
    'no_trailing_comma_in_singleline_array'            => true,
    'no_whitespace_in_blank_line'                      => true,
    'no_spaces_around_offset'                          => true,
    'no_unused_imports'                                => true,
    'no_useless_else'                                  => true,
    'no_useless_return'                                => true,
    'no_whitespace_before_comma_in_array'              => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'phpdoc_add_missing_param_annotation'              => ['only_untyped' => false],
    'phpdoc_order'                                     => true,
    'phpdoc_scalar'                                    => true,
    'phpdoc_var_without_name'                          => true,
    'pow_to_exponentiation'                            => true,
    'psr4'                                             => true,
    'random_api_migration'                             => true,
    'return_type_declaration'                          => true,
    'short_scalar_cast'                                => true,
    'simplified_null_return'                           => false,
    'single_blank_line_before_namespace'               => true,
    'single_quote'                                     => true,
    'yoda_style'                                       => true,
];
