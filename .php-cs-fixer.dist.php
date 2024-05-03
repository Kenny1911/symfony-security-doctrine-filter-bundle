<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'global_namespace_import' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_unsets' => true,
        'linebreak_after_opening_tag' => true,
        'no_php4_constructor' => true,
        'no_useless_else' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => ['imports_order' => ['const', 'class', 'function']],
        'php_unit_construct' => true,
        'php_unit_strict' => false,
        'phpdoc_no_empty_return' => false,
        'declare_strict_types' => true,
        'phpdoc_no_alias_tag' => false,
        'single_line_throw' => false,
        // 'phpdoc_inline_tag' => false,
        'general_phpdoc_tag_rename' => false,
        'phpdoc_inline_tag_normalizer' => false,
        'phpdoc_tag_type' => false,
        'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder)
;