<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('Tests/Functional/cache')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'header_comment' => [
            'header' => <<<HEADER
This file is part of the NelmioApiDocBundle package.

(c) Nelmio

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER
        ],
    ])
    ->setFinder($finder)
;
