<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Form\Extension\DescriptionFormTypeExtension;
use Nelmio\ApiDocBundle\Parser\FormTypeParser;
use Nelmio\ApiDocBundle\Tests\Fixtures;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryBuilder;
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
                        'readonly' => false
                    ),
                    'b' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => '',
                        'readonly' => false
                    ),
                    'c' => array(
                        'dataType' => 'boolean',
                        'required' => true,
                        'description' => '',
                        'readonly' => false
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
                        'readonly' => false
                    ),
                    'collection_type[b][][a]' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => 'A nice description',
                        'readonly' => false
                    ),
                    'collection_type[b][][b]' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => '',
                        'readonly' => false
                    ),
                    'collection_type[b][][c]' => array(
                        'dataType' => 'boolean',
                        'required' => true,
                        'description' => '',
                        'readonly' => false
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
                        'readonly' => false
                    ),
                    'b[][a]' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => 'A nice description',
                        'readonly' => false
                    ),
                    'b[][b]' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => '',
                        'readonly' => false
                    ),
                    'b[][c]' => array(
                        'dataType' => 'boolean',
                        'required' => true,
                        'description' => '',
                        'readonly' => false
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
                        'readonly' => false
                    ),
                    'b[][a]' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => 'A nice description',
                        'readonly' => false
                    ),
                    'b[][b]' => array(
                        'dataType' => 'string',
                        'required' => true,
                        'description' => '',
                        'readonly' => false
                    ),
                    'b[][c]' => array(
                        'dataType' => 'boolean',
                        'required' => true,
                        'description' => '',
                        'readonly' => false
                    )
                )
            ),
        );
    }
}
