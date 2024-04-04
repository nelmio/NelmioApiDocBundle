<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('tests/Functional/cache');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'header_comment' => [
            'header' => <<<HEADER
This file is part of the NelmioApiDocBundle package.

(c) Nelmio

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER
        ],
    ])
    ->setFinder($finder);
