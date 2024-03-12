<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => true,
        ],
        'phpdoc_line_span' => true,
    ])
    ->setFinder($finder)
;
