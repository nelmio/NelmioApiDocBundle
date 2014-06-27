<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\DataTypes;
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
                        'actualType' => DataTypes::STRING,
                        'subType' => null,
                        'required' => true,
                        'description' => 'A nice description',
                        'readonly' => false,
                        'default' => null
                    ),
                    'b' => array(
                        'dataType' => 'string',
                        'actualType' => DataTypes::STRING,
                        'subType' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'default' => null
                    ),
                    'c' => array(
                        'dataType' => 'boolean',
                        'actualType' => DataTypes::BOOLEAN,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'd' => array(
                        'dataType' => 'string',
                        'actualType' => DataTypes::STRING,
                        'subType' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'default' => "DefaultTest"
                    )
                )
            ),
            array(
                array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\CollectionType'),
                array(
                    'collection_type' => array(
                        'dataType' => 'object (CollectionType)',
                        'actualType' => DataTypes::MODEL,
                        'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\CollectionType',
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'children' => array(
                            'a' => array(
                                'dataType' => 'array of strings',
                                'actualType' => DataTypes::COLLECTION,
                                'subType' => DataTypes::STRING,
                                'default' => null,
                                'required' => true,
                                'description' => '',
                                'readonly' => false,
                            ),
                            'b' => array(
                                'dataType' => 'array of objects (TestType)',
                                'actualType' => DataTypes::COLLECTION,
                                'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType',
                                'default' => null,
                                'required' => true,
                                'description' => '',
                                'readonly' => false,
                                'children' => array(
                                    'a' => array(
                                        'dataType' => 'string',
                                        'actualType' => DataTypes::STRING,
                                        'default' => null,
                                        'subType' => null,
                                        'required' => true,
                                        'description' => 'A nice description',
                                        'readonly' => false,
                                    ),
                                    'b' => array(
                                        'dataType' => 'string',
                                        'actualType' => DataTypes::STRING,
                                        'default' => null,
                                        'subType' => null,
                                        'required' => true,
                                        'description' => '',
                                        'readonly' => false,
                                    ),
                                    'c' => array(
                                        'dataType' => 'boolean',
                                        'actualType' => DataTypes::BOOLEAN,
                                        'subType' => null,
                                        'default' => null,
                                        'required' => true,
                                        'description' => '',
                                        'readonly' => false,
                                    ),
                                    'd' => array(
                                        'dataType' => 'string',
                                        'actualType' => DataTypes::STRING,
                                        'subType' => null,
                                        'required' => true,
                                        'description' => '',
                                        'readonly' => false,
                                        'default' => "DefaultTest"
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\CollectionType',
                    'name' => '',
                ),
                array(
                    'a' => array(
                        'dataType' => 'array of strings',
                        'actualType' => DataTypes::COLLECTION,
                        'subType' => DataTypes::STRING,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'b' => array(
                        'dataType' => 'array of objects (TestType)',
                        'actualType' => DataTypes::COLLECTION,
                        'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType',
                        'required' => true,
                        'description' => '',
                        'default' => null,
                        'readonly' => false,
                        'children' => array(
                            'a' => array(
                                'dataType' => 'string',
                                'actualType' => DataTypes::STRING,
                                'subType' => null,
                                'default' => null,
                                'required' => true,
                                'description' => 'A nice description',
                                'readonly' => false,
                            ),
                            'b' => array(
                                'dataType' => 'string',
                                'actualType' => DataTypes::STRING,
                                'subType' => null,
                                'default' => null,
                                'required' => true,
                                'description' => '',
                                'readonly' => false,
                            ),
                            'c' => array(
                                'dataType' => 'boolean',
                                'actualType' => DataTypes::BOOLEAN,
                                'subType' => null,
                                'default' => null,
                                'required' => true,
                                'description' => '',
                                'readonly' => false,
                            ),
                            'd' => array(
                                'dataType' => 'string',
                                'actualType' => DataTypes::STRING,
                                'subType' => null,
                                'required' => true,
                                'description' => '',
                                'readonly' => false,
                                'default' => "DefaultTest"
                            ),
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\CollectionType',
                    'name' => null,
                ),
                array(
                    'a' => array(
                        'dataType' => 'array of strings',
                        'actualType' => DataTypes::COLLECTION,
                        'subType' => DataTypes::STRING,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'b' => array(
                        'dataType' => 'array of objects (TestType)',
                        'actualType' => DataTypes::COLLECTION,
                        'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType',
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'children' => array(
                            'a' => array(
                                'dataType' => 'string',
                                'actualType' => DataTypes::STRING,
                                'subType' => null,
                                'default' => null,
                                'required' => true,
                                'description' => 'A nice description',
                                'readonly' => false,
                            ),
                            'b' => array(
                                'dataType' => 'string',
                                'actualType' => DataTypes::STRING,
                                'subType' => null,
                                'default' => null,
                                'required' => true,
                                'description' => '',
                                'readonly' => false,
                            ),
                            'c' => array(
                                'dataType' => 'boolean',
                                'actualType' => DataTypes::BOOLEAN,
                                'subType' => null,
                                'default' => null,
                                'required' => true,
                                'description' => '',
                                'readonly' => false,
                            ),
                            'd' => array(
                                'dataType' => 'string',
                                'actualType' => DataTypes::STRING,
                                'subType' => null,
                                'default' => "DefaultTest",
                                'required' => true,
                                'description' => '',
                                'readonly' => false
                            ),
                        ),
                    ),
                ),
            ),
            array(
                array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\ImprovedTestType'),
                array(
                    'dt1' => array(
                        'dataType' => 'datetime',
                        'actualType' => DataTypes::DATETIME,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => 'A nice description',
                        'readonly' => false,
                        'format' => DateTimeType::HTML5_FORMAT,
                    ),
                    'dt2' => array(
                        'dataType' => 'datetime',
                        'actualType' => DataTypes::DATETIME,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => 'M/d/y',
                    ),
                    'dt3' => array(
                        'dataType' => 'datetime',
                        'actualType' => DataTypes::DATETIME,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => 'M/d/y H:i:s',
                    ),
                    'dt4' => array(
                        'dataType' => 'datetime',
                        'actualType' => DataTypes::DATETIME,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'dt5' => array(
                        'dataType' => 'datetime',
                        'actualType' => DataTypes::DATETIME,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'd1' => array(
                        'dataType' => 'date',
                        'actualType' => DataTypes::DATE,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'd2' => array(
                        'dataType' => 'date',
                        'actualType' => DataTypes::DATE,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => 'd-M-y',
                    ),
                    'c1' => array(
                        'dataType' => 'choice',
                        'actualType' => DataTypes::ENUM,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => json_encode(array('m' => 'Male', 'f' => 'Female')),
                    ),
                    'c2' => array(
                        'dataType' => 'array of choices',
                        'actualType' => DataTypes::COLLECTION,
                        'subType' => DataTypes::ENUM,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => json_encode(array('m' => 'Male', 'f' => 'Female')),
                    ),
                    'c3' => array(
                        'dataType' => 'choice',
                        'actualType' => DataTypes::ENUM,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                    ),
                    'c4' => array(
                        'dataType' => 'choice',
                        'actualType' => DataTypes::ENUM,
                        'subType' => null,
                        'default' => null,
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'format' => json_encode(array('foo' => 'bar', 'baz' => 'Buzz')),
                    ),
                ),
            ),
            array(
                array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\CompoundType'),
                array (
                    'sub_form' =>
                        array (
                            'dataType' => 'object (SimpleType)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Form\\SimpleType',
                            'default' => null,
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                            'children' =>
                                array (
                                    'a' =>
                                        array (
                                            'dataType' => 'string',
                                            'actualType' => 'string',
                                            'subType' => NULL,
                                            'default' => null,
                                            'required' => true,
                                            'description' => 'Something that describes A.',
                                            'readonly' => false,
                                        ),
                                    'b' =>
                                        array (
                                            'dataType' => 'float',
                                            'actualType' => 'float',
                                            'subType' => NULL,
                                            'default' => null,
                                            'required' => true,
                                            'description' => '',
                                            'readonly' => false,
                                        ),
                                    'c' =>
                                        array (
                                            'dataType' => 'choice',
                                            'actualType' => 'choice',
                                            'subType' => NULL,
                                            'default' => null,
                                            'required' => true,
                                            'description' => '',
                                            'readonly' => false,
                                            'format' => '{"x":"X","y":"Y","z":"Z"}',
                                        ),
                                    'd' =>
                                        array (
                                            'dataType' => 'datetime',
                                            'actualType' => 'datetime',
                                            'subType' => NULL,
                                            'default' => null,
                                            'required' => true,
                                            'description' => '',
                                            'readonly' => false,
                                        ),
                                    'e' =>
                                        array (
                                            'dataType' => 'date',
                                            'actualType' => 'date',
                                            'subType' => NULL,
                                            'default' => null,
                                            'required' => true,
                                            'description' => '',
                                            'readonly' => false,
                                        ),
                                    'g' =>
                                        array (
                                            'dataType' => 'string',
                                            'actualType' => 'string',
                                            'subType' => NULL,
                                            'default' => null,
                                            'required' => true,
                                            'description' => '',
                                            'readonly' => false,
                                        ),
                                ),
                        ),
                    'a' =>
                        array (
                            'dataType' => 'float',
                            'actualType' => 'float',
                            'subType' => NULL,
                            'default' => null,
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                ),
            ),
            array(
                array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\RequireConstructionType'),
                array(
                    'require_construction_type' => array(
                        'dataType' => 'object (RequireConstructionType)',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'default' => null,
                        'actualType' => 'model',
                        'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\RequireConstructionType',
                        'children' => array(
                            'a' => array(
                                'dataType' => 'string',
                                'actualType' => 'string',
                                'subType' => null,
                                'default' => null,
                                'required' => true,
                                'description' => 'A nice description',
                                'readonly' => false,
                            ),
                        ),
                    ),
                ),
            ),
            array(
                array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\DependencyType'),
                array(
                    'dependency_type' => array(
                        'dataType' => 'object (DependencyType)',
                        'required' => true,
                        'description' => '',
                        'readonly' => false,
                        'default' => null,
                        'actualType' => 'model',
                        'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\DependencyType',
                        'children' => array(
                            'a' => array(
                                'dataType' => 'string',
                                'actualType' => 'string',
                                'subType' => null,
                                'default' => null,
                                'required' => true,
                                'description' => 'A nice description',
                                'readonly' => false,
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
