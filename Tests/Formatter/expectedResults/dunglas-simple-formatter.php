<?php
return array(
    '/api/other-resources' => array(
        0 => array(
            'method' => 'GET',
            'uri' => '/api/other-resources.{_format}',
            'description' => 'List another resource.',
            'requirements' => array(
                '_format' => array(
                    'requirement' => 'json|xml|html',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'response' => array(
                '' => array(
                    'dataType' => 'array of objects (JmsTest)',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
                    'actualType' => 'collection',
                    'readonly' => true,
                    'required' => true,
                    'default' => true,
                    'description' => '',
                    'children' => array(
                        'foo' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'bar' => array(
                            'dataType' => 'DateTime',
                            'actualType' => 'datetime',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => true,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'number' => array(
                            'dataType' => 'double',
                            'actualType' => 'float',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'arr' => array(
                            'dataType' => 'array',
                            'actualType' => 'collection',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'nested' => array(
                            'dataType' => 'object (JmsNested)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                            'children' => array(
                                'foo' => array(
                                    'dataType' => 'DateTime',
                                    'actualType' => 'datetime',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => true,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'bar' => array(
                                    'dataType' => 'string',
                                    'actualType' => 'string',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => 'baz',
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'baz' => array(
                                    'dataType' => 'array of integers',
                                    'actualType' => 'collection',
                                    'subType' => 'integer',
                                    'required' => false,
                                    'default' => null,
                                    'description' => 'Epic description.

With multiple lines.',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'circular' => array(
                                    'dataType' => 'object (JmsNested)',
                                    'actualType' => 'model',
                                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'parent' => array(
                                    'dataType' => 'object (JmsTest)',
                                    'actualType' => 'model',
                                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                    'children' => array(
                                        'foo' => array(
                                            'dataType' => 'string',
                                            'actualType' => 'string',
                                            'subType' => null,
                                            'required' => false,
                                            'default' => null,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null
                                        ),
                                        'bar' => array(
                                            'dataType' => 'DateTime',
                                            'actualType' => 'datetime',
                                            'subType' => null,
                                            'required' => false,
                                            'default' => null,
                                            'description' => '',
                                            'readonly' => true,
                                            'sinceVersion' => null,
                                            'untilVersion' => null
                                        ),
                                        'number' => array(
                                            'dataType' => 'double',
                                            'actualType' => 'float',
                                            'subType' => null,
                                            'required' => false,
                                            'default' => null,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null
                                        ),
                                        'arr' => array(
                                            'dataType' => 'array',
                                            'actualType' => 'collection',
                                            'subType' => null,
                                            'required' => false,
                                            'default' => null,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null
                                        ),
                                        'nested' => array(
                                            'dataType' => 'object (JmsNested)',
                                            'actualType' => 'model',
                                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                            'required' => false,
                                            'default' => null,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null
                                        ),
                                        'nested_array' => array(
                                            'dataType' => 'array of objects (JmsNested)',
                                            'actualType' => 'collection',
                                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                            'required' => false,
                                            'default' => null,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null
                                        )
                                    )
                                ),
                                'since' => array(
                                    'dataType' => 'string',
                                    'actualType' => 'string',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => '0.2',
                                    'untilVersion' => null
                                ),
                                'until' => array(
                                    'dataType' => 'string',
                                    'actualType' => 'string',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => '0.3'
                                ),
                                'since_and_until' => array(
                                    'dataType' => 'string',
                                    'actualType' => 'string',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => '0.4',
                                    'untilVersion' => '0.5'
                                )
                            )
                        ),
                        'nested_array' => array(
                            'dataType' => 'array of objects (JmsNested)',
                            'actualType' => 'collection',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        )
                    )
                )
            ),
            'resourceDescription' => 'Operations on another resource.',
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'views' => array(
                'default',
                'premium'
            )
        ),
        1 => array(
            'method' => 'PUT|PATCH',
            'uri' => '/api/other-resources/{id}.{_format}',
            'description' => 'Update a resource bu ID.',
            'requirements' => array(
                '_format' => array(
                    'requirement' => 'json|xml|html',
                    'dataType' => '',
                    'description' => ''
                ),
                'id' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        )

    ),
    '/api/resources' => array(
        0 => array(
            'method' => 'GET',
            'uri' => '/api/resources.{_format}',
            'description' => 'List resources.',
            'requirements' => array(
                '_format' => array(
                    'requirement' => 'json|xml|html',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'response' => array(
                'tests' => array(
                    'dataType' => 'array of objects (Test)',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
                    'actualType' => 'collection',
                    'readonly' => true,
                    'required' => true,
                    'default' => true,
                    'description' => '',
                    'children' => array(
                        'a' => array(
                            'default' => 'nelmio',
                            'actualType' => 'string',
                            'subType' => null,
                            'format' => '{length: min: foo}, {not blank}',
                            'required' => true,
                            'dataType' => 'string',
                            'readonly' => null
                        ),
                        'b' => array(
                            'default' => null,
                            'actualType' => 'datetime',
                            'subType' => null,
                            'dataType' => 'DateTime',
                            'readonly' => null,
                            'required' => null
                        )
                    )
                )
            ),
            'statusCodes' => array(
                200 => array(
                    0 => 'Returned on success.'
                ),
                404 => array(
                    0 => 'Returned if resource cannot be found.'
                )
            ),
            'resourceDescription' => 'Operations on resource.',
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'views' => array(
                'test',
                'premium',
                'default'
            )
        ),
        1 => array(
            'method' => 'POST',
            'uri' => '/api/resources.{_format}',
            'description' => 'Create a new resource.',
            'parameters' => array(
                'a' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => 'Something that describes A.',
                    'readonly' => false
                ),
                'b' => array(
                    'dataType' => 'float',
                    'actualType' => 'float',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                ),
                'c' => array(
                    'dataType' => 'choice',
                    'actualType' => 'choice',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => null,
                    'readonly' => false,
                    'format' => '{"x":"X","y":"Y","z":"Z"}'
                ),
                'd' => array(
                    'dataType' => 'datetime',
                    'actualType' => 'datetime',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                ),
                'e' => array(
                    'dataType' => 'date',
                    'actualType' => 'date',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                ),
                'g' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                )
            ),
            'requirements' => array(
                '_format' => array(
                    'requirement' => 'json|xml|html',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'response' => array(
                'foo' => array(
                    'dataType' => 'DateTime',
                    'actualType' => 'datetime',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => true,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'bar' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'required' => false,
                    'default' => 'baz',
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'baz' => array(
                    'dataType' => 'array of integers',
                    'actualType' => 'collection',
                    'subType' => 'integer',
                    'required' => false,
                    'default' => null,
                    'description' => 'Epic description.

With multiple lines.',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'circular' => array(
                    'dataType' => 'object (JmsNested)',
                    'actualType' => 'model',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                    'children' => array(
                        'foo' => array(
                            'dataType' => 'DateTime',
                            'actualType' => 'datetime',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => true,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'bar' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => 'baz',
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'baz' => array(
                            'dataType' => 'array of integers',
                            'actualType' => 'collection',
                            'subType' => 'integer',
                            'required' => false,
                            'default' => null,
                            'description' => 'Epic description.

With multiple lines.',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'circular' => array(
                            'dataType' => 'object (JmsNested)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'parent' => array(
                            'dataType' => 'object (JmsTest)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                            'children' => array(
                                'foo' => array(
                                    'dataType' => 'string',
                                    'actualType' => 'string',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'bar' => array(
                                    'dataType' => 'DateTime',
                                    'actualType' => 'datetime',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => true,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'number' => array(
                                    'dataType' => 'double',
                                    'actualType' => 'float',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'arr' => array(
                                    'dataType' => 'array',
                                    'actualType' => 'collection',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'nested' => array(
                                    'dataType' => 'object (JmsNested)',
                                    'actualType' => 'model',
                                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'nested_array' => array(
                                    'dataType' => 'array of objects (JmsNested)',
                                    'actualType' => 'collection',
                                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                )
                            )
                        ),
                        'since' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => '0.2',
                            'untilVersion' => null
                        ),
                        'until' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => '0.3'
                        ),
                        'since_and_until' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => '0.4',
                            'untilVersion' => '0.5'
                        )
                    )
                ),
                'parent' => array(
                    'dataType' => 'object (JmsTest)',
                    'actualType' => 'model',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                    'children' => array(
                        'foo' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'bar' => array(
                            'dataType' => 'DateTime',
                            'actualType' => 'datetime',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => true,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'number' => array(
                            'dataType' => 'double',
                            'actualType' => 'float',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'arr' => array(
                            'dataType' => 'array',
                            'actualType' => 'collection',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'nested' => array(
                            'dataType' => 'object (JmsNested)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'nested_array' => array(
                            'dataType' => 'array of objects (JmsNested)',
                            'actualType' => 'collection',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        )
                    )
                ),
                'since' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => '0.2',
                    'untilVersion' => null
                ),
                'until' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => '0.3'
                ),
                'since_and_until' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => '0.4',
                    'untilVersion' => '0.5'
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'views' => array(
                'default',
                'premium'
            )
        ),
        2 => array(
            'method' => 'GET',
            'uri' => '/api/resources/{id}.{_format}',
            'description' => 'Retrieve a resource by ID.',
            'requirements' => array(
                '_format' => array(
                    'requirement' => 'json|xml|html',
                    'dataType' => '',
                    'description' => ''
                ),
                'id' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        3 => array(
            'method' => 'DELETE',
            'uri' => '/api/resources/{id}.{_format}',
            'description' => 'Delete a resource by ID.',
            'requirements' => array(
                '_format' => array(
                    'requirement' => 'json|xml|html',
                    'dataType' => '',
                    'description' => ''
                ),
                'id' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        )

    ),
    '/tests' => array(
        0 => array(
            'method' => 'GET',
            'uri' => '/tests.{_format}',
            'description' => 'index action',
            'filters' => array(
                'a' => array(
                    'dataType' => 'integer'
                ),
                'b' => array(
                    'dataType' => 'string',
                    'arbitrary' => array(
                        0 => 'arg1',
                        1 => 'arg2'
                    )
                )
            ),
            'requirements' => array(
                '_format' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        1 => array(
            'method' => 'GET',
            'uri' => '/tests.{_format}',
            'description' => 'index action',
            'filters' => array(
                'a' => array(
                    'dataType' => 'integer'
                ),
                'b' => array(
                    'dataType' => 'string',
                    'arbitrary' => array(
                        0 => 'arg1',
                        1 => 'arg2'
                    )
                )
            ),
            'requirements' => array(
                '_format' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        2 => array(
            'method' => 'POST',
            'uri' => '/tests.{_format}',
            'host' => 'api.test.dev',
            'description' => 'create test',
            'parameters' => array(
                'a' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => 'A nice description',
                    'readonly' => false
                ),
                'b' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => null,
                    'required' => false,
                    'description' => null,
                    'readonly' => false
                ),
                'c' => array(
                    'dataType' => 'boolean',
                    'actualType' => 'boolean',
                    'subType' => null,
                    'default' => false,
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                ),
                'd' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => 'DefaultTest',
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                )
            ),
            'requirements' => array(
                '_format' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'views' => array(
                'default',
                'premium'
            )
        ),
        3 => array(
            'method' => 'POST',
            'uri' => '/tests.{_format}',
            'host' => 'api.test.dev',
            'description' => 'create test',
            'parameters' => array(
                'a' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => 'A nice description',
                    'readonly' => false
                ),
                'b' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => null,
                    'required' => false,
                    'description' => null,
                    'readonly' => false
                ),
                'c' => array(
                    'dataType' => 'boolean',
                    'actualType' => 'boolean',
                    'subType' => null,
                    'default' => false,
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                ),
                'd' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => 'DefaultTest',
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                )
            ),
            'requirements' => array(
                '_format' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'views' => array(
                'default',
                'premium'
            )
        )
    ),
    '/tests2' => array(
        0 => array(
            'method' => 'POST',
            'uri' => '/tests2.{_format}',
            'description' => 'post test 2',
            'requirements' => array(
                '_format' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'views' => array(
                'default',
                'premium'
            )
        )
    ),
    'TestResource' => array(
        0 => array(
            'method' => 'ANY',
            'uri' => '/named-resource',
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'views' => array(
                'default'
            )
        )
    ),
    'others' => array(
        0 => array(
            'method' => 'POST',
            'uri' => '/another-post',
            'description' => 'create another test',
            'parameters' => array(
                'dependency_type' => array(
                    'required' => true,
                    'readonly' => false,
                    'description' => '',
                    'default' => null,
                    'dataType' => 'object (dependency_type)',
                    'actualType' => 'model',
                    'subType' => 'dependency_type',
                    'children' => array(
                        'a' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'default' => null,
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false
                        )
                    )
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'views' => array(
                'default',
                'test'
            )
        ),
        1 => array(
            'method' => 'ANY',
            'uri' => '/any',
            'description' => 'Action without HTTP verb',
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        2 => array(
            'method' => 'ANY',
            'uri' => '/any/{foo}',
            'description' => 'Action without HTTP verb',
            'requirements' => array(
                'foo' => array(
                    'requirement' => '',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        3 => array(
            'method' => 'ANY',
            'uri' => '/authenticated',
            'https' => false,
            'authentication' => true,
            'authenticationRoles' => array(
                0 => 'ROLE_USER',
                1 => 'ROLE_FOOBAR'
            ),
            'deprecated' => false
        ),
        4 => array(
            'method' => 'POST',
            'uri' => '/jms-input-test',
            'description' => 'Testing JMS',
            'parameters' => array(
                'foo' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'bar' => array(
                    'dataType' => 'DateTime',
                    'actualType' => 'datetime',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => true,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'number' => array(
                    'dataType' => 'double',
                    'actualType' => 'float',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'arr' => array(
                    'dataType' => 'array',
                    'actualType' => 'collection',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'nested' => array(
                    'dataType' => 'object (JmsNested)',
                    'actualType' => 'model',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                    'children' => array(
                        'foo' => array(
                            'dataType' => 'DateTime',
                            'actualType' => 'datetime',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => true,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'bar' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => 'baz',
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'baz' => array(
                            'dataType' => 'array of integers',
                            'actualType' => 'collection',
                            'subType' => 'integer',
                            'required' => false,
                            'default' => null,
                            'description' => 'Epic description.

With multiple lines.',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'circular' => array(
                            'dataType' => 'object (JmsNested)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'parent' => array(
                            'dataType' => 'object (JmsTest)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                            'children' => array(
                                'foo' => array(
                                    'dataType' => 'string',
                                    'actualType' => 'string',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'bar' => array(
                                    'dataType' => 'DateTime',
                                    'actualType' => 'datetime',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => true,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'number' => array(
                                    'dataType' => 'double',
                                    'actualType' => 'float',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'arr' => array(
                                    'dataType' => 'array',
                                    'actualType' => 'collection',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'nested' => array(
                                    'dataType' => 'object (JmsNested)',
                                    'actualType' => 'model',
                                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'nested_array' => array(
                                    'dataType' => 'array of objects (JmsNested)',
                                    'actualType' => 'collection',
                                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                )
                            )
                        ),
                        'since' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => '0.2',
                            'untilVersion' => null
                        ),
                        'until' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => '0.3'
                        ),
                        'since_and_until' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => '0.4',
                            'untilVersion' => '0.5'
                        )
                    )
                ),
                'nested_array' => array(
                    'dataType' => 'array of objects (JmsNested)',
                    'actualType' => 'collection',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        5 => array(
            'method' => 'GET',
            'uri' => '/jms-return-test',
            'description' => 'Testing return',
            'response' => array(
                'dependency_type' => array(
                    'required' => true,
                    'readonly' => false,
                    'description' => '',
                    'default' => null,
                    'dataType' => 'object (dependency_type)',
                    'actualType' => 'model',
                    'subType' => 'dependency_type',
                    'children' => array(
                        'a' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'default' => null,
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false
                        )
                    )
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        6 => array(
            'method' => 'ANY',
            'uri' => '/my-commented/{id}/{page}/{paramType}/{param}',
            'description' => 'This method is useful to test if the getDocComment works.',
            'documentation' => 'This method is useful to test if the getDocComment works.
And, it supports multilines until the first \'@\' char.',
            'requirements' => array(
                'id' => array(
                    'dataType' => 'int',
                    'description' => 'A nice comment',
                    'requirement' => ''
                ),
                'page' => array(
                    'dataType' => 'int',
                    'description' => '',
                    'requirement' => ''
                ),
                'paramType' => array(
                    'dataType' => 'int',
                    'description' => 'The param type',
                    'requirement' => ''
                ),
                'param' => array(
                    'dataType' => 'int',
                    'description' => 'The param id',
                    'requirement' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        7 => array(
            'method' => 'GET',
            'uri' => '/popos',
            'description' => 'Retrieves the collection of Popo resources.',
            'documentation' => 'Gets the collection.',
            'response' => array(
                'foo' => array(
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                    'dataType' => 'string'
                ),
                'id' => array(
                    'dataType' => 'integer',
                    'required' => false,
                    'description' => '',
                    'readonly' => false,),
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'resourceDescription' => 'Popo',
            'section' => 'Popo'
        ),
        8 => array(
            'method' => 'POST',
            'uri' => '/popos',
            'description' => 'Creates a Popo resource.',
            'documentation' => 'Adds an element to the collection.',
            'parameters' => array(
                'foo' => array(
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                    'dataType' => 'string'
                ),
                'id' => array(
                    'dataType' => 'integer',
                    'required' => false,
                    'description' => '',
                    'readonly' => false,),
            ),
            'response' => array(
                'foo' => array(
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                    'dataType' => 'string'
                ),
                'id' => array(
                    'dataType' => 'integer',
                    'required' => false,
                    'description' => '',
                    'readonly' => false,),
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'resourceDescription' => 'Popo',
            'section' => 'Popo'
        ),
        9 => array(
            'method' => 'GET',
            'uri' => '/popos/{id}',
            'description' => 'Retrieves Popo resource.',
            'documentation' => 'Gets an element of the collection.',
            'requirements' => array(
                'id' => array(
                    'dataType' => 'int',
                    'description' => '',
                    'requirement' => ''
                )
            ),
            'response' => array(
                'foo' => array(
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                    'dataType' => 'string'
                ),
                'id' => array(
                    'dataType' => 'integer',
                    'required' => false,
                    'description' => '',
                    'readonly' => false,),
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'resourceDescription' => 'Popo',
            'section' => 'Popo'
        ),
        10 => array(
            'method' => 'PUT',
            'uri' => '/popos/{id}',
            'description' => 'Replaces the Popo resource.',
            'documentation' => 'Replaces an element of the collection.',
            'parameters' => array(
                'foo' => array(
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                    'dataType' => 'string'
                ),
                'id' => array(
                    'dataType' => 'integer',
                    'required' => false,
                    'description' => '',
                    'readonly' => false,),
            ),
            'requirements' => array(
                'id' => array(
                    'dataType' => 'string',
                    'description' => '',
                    'requirement' => ''
                )
            ),
            'response' => array(
                'foo' => array(
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                    'dataType' => 'string'
                ),
                'id' => array(
                    'dataType' => 'integer',
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                ),
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'resourceDescription' => 'Popo',
            'section' => 'Popo'
        ),
        11 => array(
            'method' => 'DELETE',
            'uri' => '/popos/{id}',
            'description' => 'Deletes the Popo resource.',
            'documentation' => 'Deletes an element of the collection.',
            'requirements' => array(
                'id' => array(
                    'dataType' => 'string',
                    'description' => '',
                    'requirement' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false,
            'resourceDescription' => 'Popo',
            'section' => 'Popo'
        ),
        12 => array(
            'method' => 'ANY',
            'uri' => '/return-nested-output',
            'response' => array(
                'foo' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'bar' => array(
                    'dataType' => 'DateTime',
                    'actualType' => 'datetime',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => true,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'number' => array(
                    'dataType' => 'double',
                    'actualType' => 'float',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'arr' => array(
                    'dataType' => 'array',
                    'actualType' => 'collection',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'nested' => array(
                    'dataType' => 'object (JmsNested)',
                    'actualType' => 'model',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                    'children' => array(
                        'foo' => array(
                            'dataType' => 'DateTime',
                            'actualType' => 'datetime',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => true,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'bar' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => 'baz',
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'baz' => array(
                            'dataType' => 'array of integers',
                            'actualType' => 'collection',
                            'subType' => 'integer',
                            'required' => false,
                            'default' => null,
                            'description' => 'Epic description.

With multiple lines.',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'circular' => array(
                            'dataType' => 'object (JmsNested)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'parent' => array(
                            'dataType' => 'object (JmsTest)',
                            'actualType' => 'model',
                            'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                            'children' => array(
                                'foo' => array(
                                    'dataType' => 'string',
                                    'actualType' => 'string',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'bar' => array(
                                    'dataType' => 'DateTime',
                                    'actualType' => 'datetime',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => true,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'number' => array(
                                    'dataType' => 'double',
                                    'actualType' => 'float',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'arr' => array(
                                    'dataType' => 'array',
                                    'actualType' => 'collection',
                                    'subType' => null,
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'nested' => array(
                                    'dataType' => 'object (JmsNested)',
                                    'actualType' => 'model',
                                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                ),
                                'nested_array' => array(
                                    'dataType' => 'array of objects (JmsNested)',
                                    'actualType' => 'collection',
                                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                                    'required' => false,
                                    'default' => null,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null
                                )
                            )
                        ),
                        'since' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => '0.2',
                            'untilVersion' => null
                        ),
                        'until' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => '0.3'
                        ),
                        'since_and_until' => array(
                            'dataType' => 'string',
                            'actualType' => 'string',
                            'subType' => null,
                            'required' => false,
                            'default' => null,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => '0.4',
                            'untilVersion' => '0.5'
                        )
                    )
                ),
                'nested_array' => array(
                    'dataType' => 'array of objects (JmsNested)',
                    'actualType' => 'collection',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        13 => array(
            'method' => 'ANY',
            'uri' => '/secure-route',
            'https' => true,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        14 => array(
            'method' => 'ANY',
            'uri' => '/yet-another/{id}',
            'requirements' => array(
                'id' => array(
                    'requirement' => '\\d+',
                    'dataType' => '',
                    'description' => ''
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        15 => array(
            'method' => 'GET',
            'uri' => '/z-action-with-deprecated-indicator',
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => true
        ),
        16 => array(
            'method' => 'POST',
            'uri' => '/z-action-with-nullable-request-param',
            'parameters' => array(
                'param1' => array(
                    'required' => false,
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'description' => 'Param1 description.',
                    'readonly' => false
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        17 => array(
            'method' => 'GET',
            'uri' => '/z-action-with-query-param',
            'filters' => array(
                'page' => array(
                    'requirement' => '\\d+',
                    'description' => 'Page of the overview.',
                    'default' => '1'
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        18 => array(
            'method' => 'GET',
            'uri' => '/z-action-with-query-param-no-default',
            'filters' => array(
                'page' => array(
                    'requirement' => '\\d+',
                    'description' => 'Page of the overview.'
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        19 => array(
            'method' => 'GET',
            'uri' => '/z-action-with-query-param-strict',
            'requirements' => array(
                'page' => array(
                    'requirement' => '\\d+',
                    'dataType' => '',
                    'description' => 'Page of the overview.'
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        20 => array(
            'method' => 'POST',
            'uri' => '/z-action-with-request-param',
            'parameters' => array(
                'param1' => array(
                    'required' => true,
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'description' => 'Param1 description.',
                    'readonly' => false
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        21 => array(
            'method' => 'ANY',
            'uri' => '/z-return-jms-and-validator-output',
            'response' => array(
                'bar' => array(
                    'default' => null,
                    'actualType' => 'datetime',
                    'subType' => null,
                    'dataType' => 'DateTime',
                    'readonly' => null,
                    'required' => null
                ),
                'objects' => array(
                    'default' => null,
                    'actualType' => 'collection',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
                    'dataType' => 'array of objects (Test)',
                    'children' => array(
                        'a' => array(
                            'default' => 'nelmio',
                            'actualType' => 'string',
                            'subType' => null,
                            'format' => '{length: min: foo}, {not blank}',
                            'required' => true,
                            'dataType' => 'string',
                            'readonly' => null
                        ),
                        'b' => array(
                            'default' => null,
                            'actualType' => 'datetime',
                            'subType' => null,
                            'dataType' => 'DateTime',
                            'readonly' => null,
                            'required' => null
                        )
                    ),
                    'readonly' => null,
                    'required' => null
                ),
                'number' => array(
                    'dataType' => 'DateTime',
                    'actualType' => 'datetime',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'related' => array(
                    'dataType' => 'object (Test)',
                    'actualType' => 'model',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                    'children' => array(
                        'a' => array(
                            'default' => 'nelmio',
                            'actualType' => 'string',
                            'subType' => null,
                            'format' => '{length: min: foo}, {not blank}',
                            'required' => true,
                            'dataType' => 'string',
                            'readonly' => null
                        ),
                        'b' => array(
                            'default' => null,
                            'actualType' => 'datetime',
                            'subType' => null,
                            'dataType' => 'DateTime',
                            'readonly' => null,
                            'required' => null
                        )
                    )
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        22 => array(
            'method' => 'ANY',
            'uri' => '/z-return-selected-parsers-input',
            'parameters' => array(
                'a' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => null,
                    'required' => true,
                    'description' => 'A nice description',
                    'readonly' => false
                ),
                'b' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => null,
                    'required' => false,
                    'description' => null,
                    'readonly' => false
                ),
                'c' => array(
                    'dataType' => 'boolean',
                    'actualType' => 'boolean',
                    'subType' => null,
                    'default' => false,
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                ),
                'd' => array(
                    'dataType' => 'string',
                    'actualType' => 'string',
                    'subType' => null,
                    'default' => 'DefaultTest',
                    'required' => true,
                    'description' => null,
                    'readonly' => false
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        23 => array(
            'method' => 'ANY',
            'uri' => '/z-return-selected-parsers-output',
            'response' => array(
                'bar' => array(
                    'default' => null,
                    'actualType' => 'datetime',
                    'subType' => null,
                    'dataType' => 'DateTime',
                    'readonly' => null,
                    'required' => null
                ),
                'objects' => array(
                    'default' => null,
                    'actualType' => 'collection',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
                    'dataType' => 'array of objects (Test)',
                    'children' => array(
                        'a' => array(
                            'default' => 'nelmio',
                            'actualType' => 'string',
                            'subType' => null,
                            'format' => '{length: min: foo}, {not blank}',
                            'required' => true,
                            'dataType' => 'string',
                            'readonly' => null
                        ),
                        'b' => array(
                            'default' => null,
                            'actualType' => 'datetime',
                            'subType' => null,
                            'dataType' => 'DateTime',
                            'readonly' => null,
                            'required' => null
                        )
                    ),
                    'readonly' => null,
                    'required' => null
                ),
                'number' => array(
                    'dataType' => 'DateTime',
                    'actualType' => 'datetime',
                    'subType' => null,
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null
                ),
                'related' => array(
                    'dataType' => 'object (Test)',
                    'actualType' => 'model',
                    'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
                    'required' => false,
                    'default' => null,
                    'description' => '',
                    'readonly' => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                    'children' => array(
                        'a' => array(
                            'default' => 'nelmio',
                            'actualType' => 'string',
                            'subType' => null,
                            'format' => '{length: min: foo}, {not blank}',
                            'required' => true,
                            'dataType' => 'string',
                            'readonly' => null
                        ),
                        'b' => array(
                            'default' => null,
                            'actualType' => 'datetime',
                            'subType' => null,
                            'dataType' => 'DateTime',
                            'readonly' => null,
                            'required' => null
                        )
                    )
                )
            ),
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        24 => array(
            'method' => 'POST',
            'uri' => '/zcached',
            'cache' => 60,
            'https' => false,
            'authentication' => false,
            'authenticationRoles' => array(),
            'deprecated' => false
        ),
        25 => array(
            'method' => 'POST',
            'uri' => '/zsecured',
            'https' => false,
            'authentication' => true,
            'authenticationRoles' => array(),
            'deprecated' => false
        )
    )
);
