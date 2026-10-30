<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

use Nelmio\ApiDocBundle\Attribute\Spec\Model as SpecModel;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\RouteOperationTranslator;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Nelmio\ApiDocBundle\Util\LegacyTypeConverter;
use OpenApi\Annotations as OA;
use OpenApi\Assembler\OperationMethodsTranslator;
use OpenApi\Augmenter\DefaultMediaType;
use OpenApi\Augmenter\OperationTags;
use OpenApi\Augmenter\Shortcuts;
use OpenApi\Augmenter\Types;
use OpenApi\Compiler\OpenApi30Compiler;
use OpenApi\Compiler\OpenApi31Compiler;
use OpenApi\Spec as OAS;
use OpenApi\Specification;
use OpenApi\Utils\AttributeFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Reads swagger-php spec attributes (`OpenApi\Spec`) on route methods, next to the classic ones.
 *
 * Each route's attributes are assembled and compiled by swagger-php, then merged into the
 * route's operations. Runs before {@see OpenApiPhpDescriber}, so classic attributes on the same
 * method merge on top and operation ids are only generated where none was declared.
 *
 * Needs swagger-php 6.17 or later, see {@see self::isSupported()}.
 */
final class SpecAttributeDescriber implements DescriberInterface, ModelRegistryAwareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use ModelRegistryAwareTrait;
    use RouteDescriberTrait;

    /**
     * @param string[] $mediaTypes
     */
    public function __construct(
        private readonly RouteCollection $routeCollection,
        private readonly ControllerReflector $controllerReflector,
        private readonly array $mediaTypes = ['json'],
    ) {
    }

    public static function isSupported(): bool
    {
        return class_exists(OperationMethodsTranslator::class) && class_exists(DefaultMediaType::class);
    }

    public function describe(OA\OpenApi $api): void
    {
        foreach ($this->routeCollection->all() as $route) {
            $controller = $route->getDefault('_controller');
            $method = $this->controllerReflector->getReflectionMethod($controller);
            if (null === $method) {
                continue;
            }

            $class = $this->getControllerClass($controller, $method);
            $factory = $this->createFactory();
            if (!$factory->hasAttributes($method) && !$factory->hasAttributes($class)) {
                continue;
            }

            $path = $this->normalizePath($route->getPath());
            $httpMethods = $this->getSupportedHttpMethods($route);

            // read for every route, so each gets instances of its own to nest
            $classTags = [];
            $classLevel = [];
            foreach ($factory->fromReflector($class) as $attribute) {
                if ($attribute instanceof OAS\Tag) {
                    $classTags[] = $attribute;
                } else {
                    $classLevel[] = $attribute;
                }
            }

            $factory->getTranslators()->insert(new RouteOperationTranslator($path, $httpMethods, $classLevel), OperationMethodsTranslator::class);

            $specification = (new Specification())->add(...$factory->fromReflector($method), ...$classTags);

            // only the augmenters that complete what the attributes declare, with what the PHP code
            // says (a parameter attribute's name and type); the rest is nelmio's job
            (new Shortcuts())($specification);
            $types = new Types();
            // logger aware since swagger-php 6.18
            if (null !== $this->logger && $types instanceof LoggerAwareInterface) { /* @phpstan-ignore-line */
                $types->setLogger($this->logger);
            }
            $types($specification);
            (new DefaultMediaType($this->getDefaultMediaType()))($specification);
            (new OperationTags())($specification);

            $specification->getWalker()->visit(SpecModel::class, function (SpecModel $model): void {
                $definition = $model->getModel();
                $model->ref = $this->modelRegistry->register(new Model(
                    LegacyTypeConverter::createType($definition['type']),
                    $definition['groups'],
                    $definition['options'],
                    $definition['serializationContext'],
                    $definition['name'],
                ));
            });

            $compiler = str_starts_with($api->openapi, '3.0') ? new OpenApi30Compiler($this->logger) : new OpenApi31Compiler($this->logger);
            $compiled = $compiler->compile($specification);

            $classTagNames = array_map(static fn (OAS\Tag $tag): string => (string) $tag->name, $classTags);
            foreach ($compiled['paths'][$path] ?? [] as $httpMethod => $operation) {
                if (!\in_array($httpMethod, $httpMethods, true) || !\is_array($operation)) {
                    continue;
                }

                // the controller's class tags, also for a method it inherits
                if ([] !== $classTagNames) {
                    $operation['tags'] = array_values(array_unique([...$operation['tags'] ?? [], ...$classTagNames]));
                }

                Util::merge(Util::getOperation(Util::getPath($api, $path), $httpMethod), $operation);
            }

            foreach ($compiled['tags'] ?? [] as $tag) {
                Util::merge(Util::getTag($api, $tag['name']), $tag);
            }
        }
    }

    private function createFactory(): AttributeFactory
    {
        $factory = new AttributeFactory();
        if (null !== $this->logger) {
            $factory->setLogger($this->logger);
        }

        return $factory;
    }

    /**
     * The class the route names, which differs from the declaring class for an inherited method.
     *
     * @return \ReflectionClass<object>
     */
    private function getControllerClass(mixed $controller, \ReflectionMethod $method): \ReflectionClass
    {
        try {
            if (\is_array($controller) && \is_string($controller[0] ?? null) && class_exists($controller[0])) {
                return new \ReflectionClass($controller[0]);
            }
            if (\is_string($controller) && false !== $ii = strpos($controller, '::')) {
                return new \ReflectionClass(substr($controller, 0, $ii));
            }
        } catch (\ReflectionException) {
            // fall back to the declaring class
        }

        return $method->getDeclaringClass();
    }

    /**
     * @return string[]
     */
    private function getSupportedHttpMethods(Route $route): array
    {
        $methods = array_map('strtolower', $route->getMethods());

        return [] === $methods ? Util::OPERATIONS : array_values(array_intersect($methods, Util::OPERATIONS));
    }

    private function getDefaultMediaType(): string
    {
        return match ($this->mediaTypes[0] ?? 'json') {
            'json' => 'application/json',
            'xml' => 'application/xml',
            default => $this->mediaTypes[0],
        };
    }
}
