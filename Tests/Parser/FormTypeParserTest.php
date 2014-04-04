<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Form\Extension\DescriptionFormTypeExtension;
use Nelmio\ApiDocBundle\Parser\FormTypeParser;
use Nelmio\ApiDocBundle\Tests\Fixtures;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
            array(
                array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\ImprovedTestType'),
                array(
                    'dt1' => array(
                        'dataType' => 'datetime',
                        'required' => true,
                        'description' => 'A nice description',
                        'readonly' => false,
                        'format' => DateTimeType::HTML5_FORMAT,
                    ),
                    'dt2' => array(
                        'dataType' => 'datetime',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => 'M/d/y',
                    ),
                    'dt3' => array(
                        'dataType' => 'datetime',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => 'M/d/y H:i:s',
                    ),
                    'dt4' => array(
                        'dataType' => 'datetime',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'dt5' => array(
                        'dataType' => 'datetime',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'd1' => array(
                        'dataType' => 'date',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'd2' => array(
                        'dataType' => 'date',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => 'd-M-y',
                    ),
                    'c1' => array(
                        'dataType' => 'choice',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => json_encode(array('m' => 'Male', 'f' => 'Female')),
                    ),
                    'c2' => array(
                        'dataType' => 'array of choices',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => json_encode(array('m' => 'Male', 'f' => 'Female')),
                    ),
                    'c3' => array(
                        'dataType' => 'choice',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'c4' => array(
                        'dataType' => 'choice',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => json_encode(array('foo' => 'bar', 'baz' => 'Buzz')),
                    ),
                )
            ),
        );
    }
}
