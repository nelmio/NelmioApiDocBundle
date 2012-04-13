<?php

function includeIfExists($file) {
    if (file_exists($file)) {
        return include $file;
    }
}

if ((!$loader = includeIfExists(__DIR__.'/../../../../../.composer/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../vendor/.composer/autoload.php'))) {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}

spl_autoload_register(function($class) {
    if (0 === strpos($class, 'Nelmio\ApiDocBundle\\')) {
        $path = __DIR__.'/../'.implode('/', array_slice(explode('\\', $class), 2)).'.php';
        if (!stream_resolve_include_path($path)) {
            return false;
        }
        require_once $path;
        return true;
    }
});

use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader(function($class) {
    if (strpos($class, 'Nelmio\ApiDocBundle\Annotation\\') === 0) {
        $path = __DIR__.'/../'.str_replace('\\', '/', substr($class, strlen('Nelmio\ApiDocBundle\\')))   .'.php';
        require_once $path;
    }
    return class_exists($class, false);
});
