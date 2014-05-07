<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Form\Extension\DescriptionFormTypeExtension;
use Nelmio\ApiDocBundle\Parser\FormTypeParser;
use Nelmio\ApiDocBundle\Tests\Fixtures;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;

class FormTypeParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataTestParse
     */
    public function testParse($typeName, $expected)
    {
        $resolvedTypeFactory = new ResolvedFormTypeFactory();
        $formFactoryBuilder = new FormFactoryBuilder();
        $formFactoryBuilder->setResolvedTypeFactory($resolvedTypeFactory);
        $formFactoryBuilder->addExtension(new CoreExtension());
        $formFactoryBuilder->addTypeExtension(new DescriptionFormTypeExtension());
        $formFactory = $formFactoryBuilder->getFormFactory();
        $formTypeParser = new FormTypeParser($formFactory);
        $output = $formTypeParser->parse($typeName);

        $this->assertEquals($expected, $output);
    }

    public function dataTestParse()
    {
        return array(
            array(
                array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType'),
                array(
                    'a' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => 'A nice description',
                        'readonly' => false,
                        'format'   => null
                    ),
                    'b' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => null
                    ),
                    'c' => array(
                        'dataType' => 'boolean',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => null
                    ),
                    'd' => array(
                        'dataType' => 'date',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => 'yyyy-MM-dd'
                    )
                )
            ),
            array(
                array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\CollectionType'),
                array(
                    'collection_type[a]' => array(
                        'dataType' => 'array of strings',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => null
                    ),
                    'collection_type[b]' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => null
                    )
                )
            ),
            array(
                array(
                    'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\CollectionType',
                    'name' => '',
                ),
                array(
                    'a' => array(
                        'dataType' => 'array of strings',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => null
                    ),
                    'b' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => null
                    )
                )
            ),
            array(
                array(
                    'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\CollectionType',
                    'name' => null,
                ),
                array(
                    'a' => array(
                        'dataType' => 'array of strings',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => null
                    ),
                    'b' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format'   => null
                    )
                )
            ),
        );
    }
}
