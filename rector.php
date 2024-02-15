<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Annotation',
        __DIR__ . '/Command',
        __DIR__ . '/Controller',
        __DIR__ . '/DependencyInjection',
        __DIR__ . '/Describer',
        __DIR__ . '/Exception',
        __DIR__ . '/Form',
        __DIR__ . '/Model',
        __DIR__ . '/ModelDescriber',
        __DIR__ . '/OpenApiPhp',
        __DIR__ . '/Processor',
        __DIR__ . '/PropertyDescriber',
        __DIR__ . '/Render',
        __DIR__ . '/RouteDescriber',
        __DIR__ . '/Routing',
        __DIR__ . '/Tests',
        __DIR__ . '/Util',
    ])
    ->withRootFiles()
    ->withPhpSets()
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ]);
