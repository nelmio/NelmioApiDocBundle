<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Describer;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Parameter;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\RouteDescriber\FosRestDescriber;
use ReflectionClass;

class FosRestDescriberTest extends AbstractDescriberTest
{
    public function testDisplayPatternInDescription()
    {
        $queryParamAnnotation = new QueryParam();
        $queryParamAnnotation->requirements = '^[a-zA-Z]{1,2}$';

        $parameter = new Parameter(['name' => 'countryCode', 'in' => 'query']);
        $parameter->setDescription('Country code filter');

        $annotationReaderMock = $this->createMock(Reader::class);

        $describer = new FosRestDescriber($annotationReaderMock);
        $reflectionClass = new ReflectionClass($describer);

        $method = $reflectionClass->getMethod('addPatternToDescription');
        $method->setAccessible(true);

        $method->invoke($describer, $parameter, $queryParamAnnotation);

        self::assertEquals("Country code filter \r\n Pattern: ^[a-zA-Z]{1,2}$", $parameter->getDescription());
    }

    public function testDisplayPatternInDescriptionButIsNull()
    {
        $queryParamAnnotation = new QueryParam();
        $queryParamAnnotation->requirements = null;

        $parameter = new Parameter(['name' => 'countryCode', 'in' => 'query']);
        $parameter->setDescription('Country code filter');

        $annotationReaderMock = $this->createMock(Reader::class);

        $describer = new FosRestDescriber($annotationReaderMock);
        $reflectionClass = new ReflectionClass($describer);

        $method = $reflectionClass->getMethod('addPatternToDescription');
        $method->setAccessible(true);

        $method->invoke($describer, $parameter, $queryParamAnnotation);

        self::assertEquals("Country code filter", $parameter->getDescription());
    }
}
