<?php

 /**
  * This file is part of the NelmioApiDoc project.
  *
  * (c) BRAMILLE Sébastien <sebastien.bramille@gmail.com>
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Nelmio\ApiDocBundle\Tests\Factory;

use Nelmio\ApiDocBundle\Factory\ApiDocFactory;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ApiDocFactoryTest
 */
class ApiDocFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Initializer with provider
     *
     * @param array $provider
     *
     * @return ApiDocFactory
     */
    protected function initializeApiDocFactory($provider = array())
    {
        $route = $this->getMockBuilder('Symfony\Component\Routing\Route')
                      ->disableOriginalConstructor()
                      ->getMock();

        $expressionFunctionProvider = $this->getMock('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface');
        $routeCollection            = $this->getMockBuilder('Symfony\Component\Routing\RouteCollection')->getMock();
        if (!empty($provider['route'])) {
            $routeCollection
                ->expects($this->any())
                ->method('get')
                ->with($provider['route'])
                ->willReturn($route);
        }

        $router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
                       ->disableOriginalConstructor()
                       ->getMock();
        $router->expects($this->any())
               ->method('getRouteCollection')
               ->willReturn($routeCollection);

        $apiDocFactory = new ApiDocFactory($router);

        return $apiDocFactory;
    }

    /**
     * @param array $provider
     *
     * @dataProvider provider
     */
    public function testCreateWithDefaultView($provider)
    {
        $output  = $this->initializeApiDocFactory($provider)->create($provider, ApiDoc::DEFAULT_VIEW);

        $this->launchTest($provider, $output);
    }

    /**
     * @param array $provider
     *
     * @dataProvider provider
     */
    public function testCreateWithPremiumView($provider)
    {
        $view   = 'premium';
        $output = $this->initializeApiDocFactory($provider)->create($provider, $view);

        $this->launchTest($provider, $output, $view);
    }

    /**
     * @param array  $provider
     * @param array  $output
     */
    protected function launchTest($provider, $output, $view = ApiDoc::DEFAULT_VIEW)
    {
        if (array_key_exists('views', $provider) && !in_array($view, $provider['views'])) {
            $this->assertFalse($output);
            return;
        }

        if (!array_key_exists('views', $provider) && $view !== ApiDoc::DEFAULT_VIEW) {
            $this->assertFalse($output);
            return;
        }

        /** var ApiDoc $apiDoc */
        $apiDoc = $output['annotation'];

        $this->assertArrayHasKey('resource', $output);
        $this->assertEquals($provider['resource'], $output['resource']);
        $this->assertArrayHasKey('annotation', $output);
        $testRequirements = $this->requirementsBuilder($provider['requirements']);
        $this->assertEquals($testRequirements, $apiDoc->getRequirements());
        $testParameters = $this->parametersBuilder($provider['parameters']);
        $this->assertEquals($testParameters, $apiDoc->getParameters());
        if (!empty($provider['route'])) {
            $this->assertInstanceOf('Symfony\Component\Routing\Route', $apiDoc->getRoute());
        }
        $this->assertEquals($provider['description'], $apiDoc->getDescription());
        $this->assertEquals($provider['section'], $apiDoc->getSection());
        $this->assertEquals($provider['output'], $apiDoc->getOutput());
    }

    /**
     * Build parameter array
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function parametersBuilder(array $parameters)
    {
        $testParameters = array();
        foreach ($parameters as $parameter) {
            $testParameters[$parameter['name']] = array(
                'dataType'    => $parameter['dataType'],
                'required'    => $parameter['required'],
                'description' => $parameter['description'],
            );
        }

        return $testParameters;
    }

    /**
     * Build requirements array
     *
     * @param array $requirements
     *
     * @return array
     */
    protected function requirementsBuilder(array $requirements)
    {
        $testRequirements = array();
        foreach ($requirements as $requirement) {
            $testRequirements[$requirement['name']] = array(
                'dataType'    => $requirement['dataType'],
                'requirement' => $requirement['requirement'],
                'description' => $requirement['description'],
            );
        }

        return $testRequirements;
    }

    /**
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                array(
                    'section'      => 'Section test',
                    'resource'     => false,
                    'description'  => 'Description test',
                    'requirements' => array(
                        array(
                            'name'        => 'foo',
                            'dataType'    =>  'string',
                            'requirement' =>  '\s+',
                            'description' =>  'foo requirements'
                        )
                    ),
                    'parameters' => array(
                        array(
                            'name'        =>  'foo',
                            'dataType'    =>  'string',
                            'required'    =>  false,
                            'description' =>  'foo parameter'
                        ),
                        array(
                            'name'        =>  'bar',
                            'dataType'    =>  'string',
                            'required'    =>  true,
                            'description' =>  'bar parameter'
                        ),
                    ),
                    'output' => 'Symfony\Component\HttpFoundation\Response'
                )
            ), array(
                array(
                    'section'      => 'Section test2',
                    'resource'     => true,
                    'description'  => 'Description test2',
                    'route'        => 'route_test',
                    'views'        => array('default'),
                    'requirements' => array(
                        array(
                            'name'        => 'foo',
                            'dataType'    =>  'string',
                            'requirement' =>  '\s+',
                            'description' =>  'foo requirements'
                        ),
                        array(
                            'name'        => 'bar',
                            'dataType'    =>  'string',
                            'requirement' =>  '\s+',
                            'description' =>  'foo requirements'
                        )
                    ),
                    'parameters' => array(
                        array(
                            'name'        =>  'bar',
                            'dataType'    =>  'string',
                            'required'    =>  true,
                            'description' =>  'bar parameter'
                        ),
                    ),
                    'output' => 'Symfony\Component\HttpFoundation\RedirectResponse'
                )
            ),
            array(
                array(
                    'section'      => 'Section test2',
                    'resource'     => true,
                    'description'  => 'Description test2',
                    'route'        => 'route_test',
                    'views'        => array('premium'),
                    'requirements' => array(
                        array(
                            'name'        => 'bar',
                            'dataType'    =>  'string',
                            'requirement' =>  '\s+',
                            'description' =>  'foo requirements'
                        )
                    ),
                    'parameters' => array(
                        array(
                            'name'        =>  'bar',
                            'dataType'    =>  'string',
                            'required'    =>  true,
                            'description' =>  'bar parameter'
                        ),
                        array(
                            'name'        => 'foo',
                            'dataType'    =>  'string',
                            'required'    =>  false,
                            'description' =>  'foo requirements'
                        ),
                    ),
                    'output' => 'Symfony\Component\HttpFoundation\RedirectResponse'
                )
            ),
            array(
                array(
                    'section'      => 'Section test2',
                    'resource'     => true,
                    'description'  => 'Description test2',
                    'route'        => 'route_test',
                    'views'        => array('premium', 'default'),
                    'requirements' => array(
                        array(
                            'name'        => 'bar',
                            'dataType'    =>  'string',
                            'requirement' =>  '\s+',
                            'description' =>  'foo requirements'
                        )
                    ),
                    'parameters' => array(
                        array(
                            'name'        =>  'bar',
                            'dataType'    =>  'string',
                            'required'    =>  true,
                            'description' =>  'bar parameter'
                        ),
                        array(
                            'name'        => 'foo',
                            'dataType'    =>  'string',
                            'required'    =>  false,
                            'description' =>  'foo requirements'
                        ),
                    ),
                    'output' => 'Symfony\Component\HttpFoundation\RedirectResponse'
                )
            )
        );
    }
}
