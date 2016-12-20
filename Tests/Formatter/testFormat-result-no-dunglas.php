<?php

use Nelmio\ApiDocBundle\Util\LegacyFormHelper;

return array (
  '/api/other-resources' =>
  array (
    0 =>
    array (
      'method' => 'GET',
      'uri' => '/api/other-resources.{_format}',
      'description' => 'List another resource.',
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => 'json|xml|html',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'views' =>
      array (
        0 => 'default',
        1 => 'premium',
      ),
      'response' =>
      array (
        '' =>
        array (
          'dataType' => 'array of objects (JmsTest)',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
          'actualType' => 'collection',
          'readonly' => true,
          'required' => true,
          'default' => true,
          'description' => '',
          'children' =>
          array (
            'foo' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'bar' =>
            array (
              'dataType' => 'DateTime',
              'actualType' => 'datetime',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => true,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'number' =>
            array (
              'dataType' => 'double',
              'actualType' => 'float',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'arr' =>
            array (
              'dataType' => 'array',
              'actualType' => 'collection',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'nested' =>
            array (
              'dataType' => 'object (JmsNested)',
              'actualType' => 'model',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
              'children' =>
              array (
                'foo' =>
                array (
                  'dataType' => 'DateTime',
                  'actualType' => 'datetime',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => true,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'bar' =>
                array (
                  'dataType' => 'string',
                  'actualType' => 'string',
                  'subType' => NULL,
                  'required' => false,
                  'default' => 'baz',
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'baz' =>
                array (
                  'dataType' => 'array of integers',
                  'actualType' => 'collection',
                  'subType' => 'integer',
                  'required' => false,
                  'default' => NULL,
                  'description' => 'Epic description.

With multiple lines.',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'circular' =>
                array (
                  'dataType' => 'object (JmsNested)',
                  'actualType' => 'model',
                  'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'parent' =>
                array (
                  'dataType' => 'object (JmsTest)',
                  'actualType' => 'model',
                  'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                  'children' =>
                  array (
                    'foo' =>
                    array (
                      'dataType' => 'string',
                      'actualType' => 'string',
                      'subType' => NULL,
                      'required' => false,
                      'default' => NULL,
                      'description' => '',
                      'readonly' => false,
                      'sinceVersion' => NULL,
                      'untilVersion' => NULL,
                    ),
                    'bar' =>
                    array (
                      'dataType' => 'DateTime',
                      'actualType' => 'datetime',
                      'subType' => NULL,
                      'required' => false,
                      'default' => NULL,
                      'description' => '',
                      'readonly' => true,
                      'sinceVersion' => NULL,
                      'untilVersion' => NULL,
                    ),
                    'number' =>
                    array (
                      'dataType' => 'double',
                      'actualType' => 'float',
                      'subType' => NULL,
                      'required' => false,
                      'default' => NULL,
                      'description' => '',
                      'readonly' => false,
                      'sinceVersion' => NULL,
                      'untilVersion' => NULL,
                    ),
                    'arr' =>
                    array (
                      'dataType' => 'array',
                      'actualType' => 'collection',
                      'subType' => NULL,
                      'required' => false,
                      'default' => NULL,
                      'description' => '',
                      'readonly' => false,
                      'sinceVersion' => NULL,
                      'untilVersion' => NULL,
                    ),
                    'nested' =>
                    array (
                      'dataType' => 'object (JmsNested)',
                      'actualType' => 'model',
                      'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                      'required' => false,
                      'default' => NULL,
                      'description' => '',
                      'readonly' => false,
                      'sinceVersion' => NULL,
                      'untilVersion' => NULL,
                    ),
                    'nested_array' =>
                    array (
                      'dataType' => 'array of objects (JmsNested)',
                      'actualType' => 'collection',
                      'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                      'required' => false,
                      'default' => NULL,
                      'description' => '',
                      'readonly' => false,
                      'sinceVersion' => NULL,
                      'untilVersion' => NULL,
                    ),
                  ),
                ),
                'since' =>
                array (
                  'dataType' => 'string',
                  'actualType' => 'string',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => '0.2',
                  'untilVersion' => NULL,
                ),
                'until' =>
                array (
                  'dataType' => 'string',
                  'actualType' => 'string',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => '0.3',
                ),
                'since_and_until' =>
                array (
                  'dataType' => 'string',
                  'actualType' => 'string',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => '0.4',
                  'untilVersion' => '0.5',
                ),
              ),
            ),
            'nested_array' =>
            array (
              'dataType' => 'array of objects (JmsNested)',
              'actualType' => 'collection',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
          ),
        ),
      ),
      'resourceDescription' => 'Operations on another resource.',
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    1 =>
    array (
      'method' => 'PUT|PATCH',
      'uri' => '/api/other-resources/{id}.{_format}',
      'description' => 'Update a resource bu ID.',
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => 'json|xml|html',
          'dataType' => '',
          'description' => '',
        ),
        'id' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
  ),
  '/api/resources' =>
  array (
    0 =>
    array (
      'method' => 'GET',
      'uri' => '/api/resources.{_format}',
      'description' => 'List resources.',
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => 'json|xml|html',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'views' =>
      array (
        0 => 'test',
        1 => 'premium',
        2 => 'default',
      ),
      'response' =>
      array (
        'tests' =>
        array (
          'dataType' => 'array of objects (Test)',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
          'actualType' => 'collection',
          'readonly' => true,
          'required' => true,
          'default' => true,
          'description' => '',
          'children' =>
          array (
            'a' =>
            array (
              'default' => 'nelmio',
              'actualType' => 'string',
              'subType' => NULL,
              'format' => '{length: min: foo}, {not blank}',
              'required' => true,
              'dataType' => 'string',
              'readonly' => NULL,
            ),
            'b' =>
            array (
              'default' => NULL,
              'actualType' => 'datetime',
              'subType' => NULL,
              'dataType' => 'DateTime',
              'readonly' => NULL,
              'required' => NULL,
            ),
          ),
        ),
      ),
      'statusCodes' =>
      array (
        200 =>
        array (
          0 => 'Returned on success.',
        ),
        404 =>
        array (
          0 => 'Returned if resource cannot be found.',
        ),
      ),
      'resourceDescription' => 'Operations on resource.',
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    1 =>
    array (
      'method' => 'POST',
      'uri' => '/api/resources.{_format}',
      'description' => 'Create a new resource.',
      'parameters' =>
      array (
        'a' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => 'Something that describes A.',
          'readonly' => false,
        ),
        'b' =>
        array (
          'dataType' => 'float',
          'actualType' => 'float',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
        'c' =>
        array (
          'dataType' => 'choice',
          'actualType' => 'choice',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => NULL,
          'readonly' => false,
          'format' => '{"x":"X","y":"Y","z":"Z"}',
        ),
        'd' =>
        array (
          'dataType' => 'datetime',
          'actualType' => 'datetime',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
        'e' =>
        array (
          'dataType' => 'date',
          'actualType' => 'date',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
        'g' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
      ),
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => 'json|xml|html',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'views' =>
      array (
        0 => 'default',
        1 => 'premium',
      ),
      'response' =>
      array (
        'foo' =>
        array (
          'dataType' => 'DateTime',
          'actualType' => 'datetime',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => true,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'bar' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'required' => false,
          'default' => 'baz',
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'baz' =>
        array (
          'dataType' => 'array of integers',
          'actualType' => 'collection',
          'subType' => 'integer',
          'required' => false,
          'default' => NULL,
          'description' => 'Epic description.

With multiple lines.',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'circular' =>
        array (
          'dataType' => 'object (JmsNested)',
          'actualType' => 'model',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
          'children' =>
          array (
            'foo' =>
            array (
              'dataType' => 'DateTime',
              'actualType' => 'datetime',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => true,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'bar' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => 'baz',
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'baz' =>
            array (
              'dataType' => 'array of integers',
              'actualType' => 'collection',
              'subType' => 'integer',
              'required' => false,
              'default' => NULL,
              'description' => 'Epic description.

With multiple lines.',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'circular' =>
            array (
              'dataType' => 'object (JmsNested)',
              'actualType' => 'model',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'parent' =>
            array (
              'dataType' => 'object (JmsTest)',
              'actualType' => 'model',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
              'children' =>
              array (
                'foo' =>
                array (
                  'dataType' => 'string',
                  'actualType' => 'string',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'bar' =>
                array (
                  'dataType' => 'DateTime',
                  'actualType' => 'datetime',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => true,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'number' =>
                array (
                  'dataType' => 'double',
                  'actualType' => 'float',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'arr' =>
                array (
                  'dataType' => 'array',
                  'actualType' => 'collection',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'nested' =>
                array (
                  'dataType' => 'object (JmsNested)',
                  'actualType' => 'model',
                  'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'nested_array' =>
                array (
                  'dataType' => 'array of objects (JmsNested)',
                  'actualType' => 'collection',
                  'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
              ),
            ),
            'since' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => '0.2',
              'untilVersion' => NULL,
            ),
            'until' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => '0.3',
            ),
            'since_and_until' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => '0.4',
              'untilVersion' => '0.5',
            ),
          ),
        ),
        'parent' =>
        array (
          'dataType' => 'object (JmsTest)',
          'actualType' => 'model',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
          'children' =>
          array (
            'foo' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'bar' =>
            array (
              'dataType' => 'DateTime',
              'actualType' => 'datetime',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => true,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'number' =>
            array (
              'dataType' => 'double',
              'actualType' => 'float',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'arr' =>
            array (
              'dataType' => 'array',
              'actualType' => 'collection',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'nested' =>
            array (
              'dataType' => 'object (JmsNested)',
              'actualType' => 'model',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'nested_array' =>
            array (
              'dataType' => 'array of objects (JmsNested)',
              'actualType' => 'collection',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
          ),
        ),
        'since' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => '0.2',
          'untilVersion' => NULL,
        ),
        'until' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => '0.3',
        ),
        'since_and_until' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => '0.4',
          'untilVersion' => '0.5',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    2 =>
    array (
      'method' => 'DELETE',
      'uri' => '/api/resources/{id}.{_format}',
      'description' => 'Delete a resource by ID.',
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => 'json|xml|html',
          'dataType' => '',
          'description' => '',
        ),
        'id' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    3 =>
    array (
      'method' => 'GET',
      'uri' => '/api/resources/{id}.{_format}',
      'description' => 'Retrieve a resource by ID.',
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => 'json|xml|html',
          'dataType' => '',
          'description' => '',
        ),
        'id' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
  ),
  '/tests' =>
  array (
    0 =>
    array (
      'method' => 'GET',
      'uri' => '/tests.{_format}',
      'description' => 'index action',
      'filters' =>
      array (
        'a' =>
        array (
          'dataType' => 'integer',
        ),
        'b' =>
        array (
          'dataType' => 'string',
          'arbitrary' =>
          array (
            0 => 'arg1',
            1 => 'arg2',
          ),
        ),
      ),
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    1 =>
    array (
      'method' => 'GET',
      'uri' => '/tests.{_format}',
      'description' => 'index action',
      'filters' =>
      array (
        'a' =>
        array (
          'dataType' => 'integer',
        ),
        'b' =>
        array (
          'dataType' => 'string',
          'arbitrary' =>
          array (
            0 => 'arg1',
            1 => 'arg2',
          ),
        ),
      ),
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    2 =>
    array (
      'method' => 'POST',
      'uri' => '/tests.{_format}',
      'host' => 'api.test.dev',
      'description' => 'create test',
      'parameters' =>
      array (
        'a' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => 'A nice description',
          'readonly' => false,
        ),
        'b' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => NULL,
          'required' => false,
          'description' => NULL,
          'readonly' => false,
        ),
        'c' =>
        array (
          'dataType' => 'boolean',
          'actualType' => 'boolean',
          'subType' => NULL,
          'default' => false,
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
        'd' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => 'DefaultTest',
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
      ),
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'views' =>
      array (
        0 => 'default',
        1 => 'premium',
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    3 =>
    array (
      'method' => 'POST',
      'uri' => '/tests.{_format}',
      'host' => 'api.test.dev',
      'description' => 'create test',
      'parameters' =>
      array (
        'a' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => 'A nice description',
          'readonly' => false,
        ),
        'b' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => NULL,
          'required' => false,
          'description' => NULL,
          'readonly' => false,
        ),
        'c' =>
        array (
          'dataType' => 'boolean',
          'actualType' => 'boolean',
          'subType' => NULL,
          'default' => false,
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
        'd' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => 'DefaultTest',
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
      ),
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'views' =>
      array (
        0 => 'default',
        1 => 'premium',
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
  ),
  '/tests2' =>
  array (
    0 =>
    array (
      'method' => 'POST',
      'uri' => '/tests2.{_format}',
      'description' => 'post test 2',
      'requirements' =>
      array (
        '_format' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'views' =>
      array (
        0 => 'default',
        1 => 'premium',
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
  ),
  'TestResource' =>
  array (
    0 =>
    array (
      'method' => 'ANY',
      'uri' => '/named-resource',
      'views' =>
      array (
        0 => 'default',
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
  ),
  'others' =>
  array (
    0 =>
    array (
      'method' => 'POST',
      'uri' => '/another-post',
      'description' => 'create another test',
      'parameters' =>
      array (
        'dependency_type' =>
        array (
          'required' => true,
          'readonly' => false,
          'description' => '',
          'default' => NULL,
          'dataType' => 'object ('.
              (LegacyFormHelper::isLegacy() ? 'dependency_type' : 'DependencyType')
          .')',
          'actualType' => 'model',
          'subType' => LegacyFormHelper::isLegacy() ? 'dependency_type' : 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\DependencyType',
          'children' =>
          array (
            'a' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'default' => NULL,
              'required' => true,
              'description' => 'A nice description',
              'readonly' => false,
            ),
          ),
        ),
      ),
      'views' =>
      array (
        0 => 'default',
        1 => 'test',
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    1 =>
    array (
      'method' => 'ANY',
      'uri' => '/any',
      'description' => 'Action without HTTP verb',
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    2 =>
    array (
      'method' => 'ANY',
      'uri' => '/any/{foo}',
      'description' => 'Action without HTTP verb',
      'requirements' =>
      array (
        'foo' =>
        array (
          'requirement' => '',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    3 =>
    array (
      'method' => 'ANY',
      'uri' => '/authenticated',
      'https' => false,
      'authentication' => true,
      'authenticationRoles' =>
      array (
        0 => 'ROLE_USER',
        1 => 'ROLE_FOOBAR',
      ),
      'deprecated' => false,
    ),
    4 =>
    array (
      'method' => 'POST',
      'uri' => '/jms-input-test',
      'description' => 'Testing JMS',
      'parameters' =>
      array (
        'foo' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'bar' =>
        array (
          'dataType' => 'DateTime',
          'actualType' => 'datetime',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => true,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'number' =>
        array (
          'dataType' => 'double',
          'actualType' => 'float',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'arr' =>
        array (
          'dataType' => 'array',
          'actualType' => 'collection',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'nested' =>
        array (
          'dataType' => 'object (JmsNested)',
          'actualType' => 'model',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
          'children' =>
          array (
            'foo' =>
            array (
              'dataType' => 'DateTime',
              'actualType' => 'datetime',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => true,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'bar' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => 'baz',
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'baz' =>
            array (
              'dataType' => 'array of integers',
              'actualType' => 'collection',
              'subType' => 'integer',
              'required' => false,
              'default' => NULL,
              'description' => 'Epic description.

With multiple lines.',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'circular' =>
            array (
              'dataType' => 'object (JmsNested)',
              'actualType' => 'model',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'parent' =>
            array (
              'dataType' => 'object (JmsTest)',
              'actualType' => 'model',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
              'children' =>
              array (
                'foo' =>
                array (
                  'dataType' => 'string',
                  'actualType' => 'string',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'bar' =>
                array (
                  'dataType' => 'DateTime',
                  'actualType' => 'datetime',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => true,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'number' =>
                array (
                  'dataType' => 'double',
                  'actualType' => 'float',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'arr' =>
                array (
                  'dataType' => 'array',
                  'actualType' => 'collection',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'nested' =>
                array (
                  'dataType' => 'object (JmsNested)',
                  'actualType' => 'model',
                  'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'nested_array' =>
                array (
                  'dataType' => 'array of objects (JmsNested)',
                  'actualType' => 'collection',
                  'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
              ),
            ),
            'since' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => '0.2',
              'untilVersion' => NULL,
            ),
            'until' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => '0.3',
            ),
            'since_and_until' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => '0.4',
              'untilVersion' => '0.5',
            ),
          ),
        ),
        'nested_array' =>
        array (
          'dataType' => 'array of objects (JmsNested)',
          'actualType' => 'collection',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    5 =>
    array (
      'method' => 'GET',
      'uri' => '/jms-return-test',
      'description' => 'Testing return',
      'response' =>
      array (
        'dependency_type' =>
        array (
          'required' => true,
          'readonly' => false,
          'description' => '',
          'default' => NULL,
          'dataType' => 'object ('.
              (LegacyFormHelper::isLegacy() ? 'dependency_type' : 'DependencyType')
          .')',
          'actualType' => 'model',
          'subType' => LegacyFormHelper::isLegacy() ? 'dependency_type' : 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\DependencyType',
          'children' =>
          array (
            'a' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'default' => NULL,
              'required' => true,
              'description' => 'A nice description',
              'readonly' => false,
            ),
          ),
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    6 =>
    array (
      'method' => 'ANY',
      'uri' => '/my-commented/{id}/{page}/{paramType}/{param}',
      'description' => 'This method is useful to test if the getDocComment works.',
      'documentation' => 'This method is useful to test if the getDocComment works.
And, it supports multilines until the first \'@\' char.',
      'requirements' =>
      array (
        'id' =>
        array (
          'dataType' => 'int',
          'description' => 'A nice comment',
          'requirement' => '',
        ),
        'page' =>
        array (
          'dataType' => 'int',
          'description' => '',
          'requirement' => '',
        ),
        'paramType' =>
        array (
          'dataType' => 'int',
          'description' => 'The param type',
          'requirement' => '',
        ),
        'param' =>
        array (
          'dataType' => 'int',
          'description' => 'The param id',
          'requirement' => '',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    7 =>
    array (
      'method' => 'ANY',
      'uri' => '/return-nested-output',
      'response' =>
      array (
        'foo' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'bar' =>
        array (
          'dataType' => 'DateTime',
          'actualType' => 'datetime',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => true,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'number' =>
        array (
          'dataType' => 'double',
          'actualType' => 'float',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'arr' =>
        array (
          'dataType' => 'array',
          'actualType' => 'collection',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'nested' =>
        array (
          'dataType' => 'object (JmsNested)',
          'actualType' => 'model',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
          'children' =>
          array (
            'foo' =>
            array (
              'dataType' => 'DateTime',
              'actualType' => 'datetime',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => true,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'bar' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => 'baz',
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'baz' =>
            array (
              'dataType' => 'array of integers',
              'actualType' => 'collection',
              'subType' => 'integer',
              'required' => false,
              'default' => NULL,
              'description' => 'Epic description.

With multiple lines.',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'circular' =>
            array (
              'dataType' => 'object (JmsNested)',
              'actualType' => 'model',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
            ),
            'parent' =>
            array (
              'dataType' => 'object (JmsTest)',
              'actualType' => 'model',
              'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsTest',
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => NULL,
              'children' =>
              array (
                'foo' =>
                array (
                  'dataType' => 'string',
                  'actualType' => 'string',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'bar' =>
                array (
                  'dataType' => 'DateTime',
                  'actualType' => 'datetime',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => true,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'number' =>
                array (
                  'dataType' => 'double',
                  'actualType' => 'float',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'arr' =>
                array (
                  'dataType' => 'array',
                  'actualType' => 'collection',
                  'subType' => NULL,
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'nested' =>
                array (
                  'dataType' => 'object (JmsNested)',
                  'actualType' => 'model',
                  'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
                'nested_array' =>
                array (
                  'dataType' => 'array of objects (JmsNested)',
                  'actualType' => 'collection',
                  'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
                  'required' => false,
                  'default' => NULL,
                  'description' => '',
                  'readonly' => false,
                  'sinceVersion' => NULL,
                  'untilVersion' => NULL,
                ),
              ),
            ),
            'since' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => '0.2',
              'untilVersion' => NULL,
            ),
            'until' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => NULL,
              'untilVersion' => '0.3',
            ),
            'since_and_until' =>
            array (
              'dataType' => 'string',
              'actualType' => 'string',
              'subType' => NULL,
              'required' => false,
              'default' => NULL,
              'description' => '',
              'readonly' => false,
              'sinceVersion' => '0.4',
              'untilVersion' => '0.5',
            ),
          ),
        ),
        'nested_array' =>
        array (
          'dataType' => 'array of objects (JmsNested)',
          'actualType' => 'collection',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\JmsNested',
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    8 =>
      array (
        'method' => 'GET',
        'uri' => '/route_with_host.{_format}',
        'host' => 'api.test.dev',
        'description' => 'Route with host placeholder',
        'requirements' =>
        array (
          'domain' =>
          array (
              'requirement' => 'test.dev|test.com',
              'dataType' => '',
              'description' => '',
          ),
        '_format' =>
          array (
            'requirement' => '',
            'dataType' => '',
            'description' => '',
          ),
      ),
      'views' =>
      array (
          0 => 'default',
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
          array (
          ),
      'deprecated' => false,
    ),
    9 =>
    array (
      'method' => 'ANY',
      'uri' => '/secure-route',
      'https' => true,
      'authentication' => false,
      'authenticationRoles' =>
        array (
        ),
      'deprecated' => false,
    ),
    10 =>
    array (
      'method' => 'ANY',
      'uri' => '/yet-another/{id}',
      'requirements' =>
      array (
        'id' =>
        array (
          'requirement' => '\\d+',
          'dataType' => '',
          'description' => '',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    11 =>
    array (
      'method' => 'GET',
      'uri' => '/z-action-with-deprecated-indicator',
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => true,
    ),
    12 =>
    array (
      'method' => 'POST',
      'uri' => '/z-action-with-nullable-request-param',
      'parameters' =>
      array (
        'param1' =>
        array (
          'required' => false,
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'description' => 'Param1 description.',
          'readonly' => false,
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    13 =>
    array (
      'method' => 'GET',
      'uri' => '/z-action-with-query-param',
      'filters' =>
      array (
        'page' =>
        array (
          'requirement' => '\\d+',
          'description' => 'Page of the overview.',
          'default' => '1',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    14 =>
    array (
      'method' => 'GET',
      'uri' => '/z-action-with-query-param-no-default',
      'filters' =>
      array (
        'page' =>
        array (
          'requirement' => '\\d+',
          'description' => 'Page of the overview.',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    15 =>
    array (
      'method' => 'GET',
      'uri' => '/z-action-with-query-param-strict',
      'requirements' =>
      array (
        'page' =>
        array (
          'requirement' => '\\d+',
          'dataType' => '',
          'description' => 'Page of the overview.',
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    16 =>
    array (
      'method' => 'POST',
      'uri' => '/z-action-with-request-param',
      'parameters' =>
      array (
        'param1' =>
        array (
          'required' => true,
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'description' => 'Param1 description.',
          'readonly' => false,
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    17 =>
    array (
      'method' => 'ANY',
      'uri' => '/z-return-jms-and-validator-output',
      'response' =>
      array (
        'bar' =>
        array (
          'default' => NULL,
          'actualType' => 'datetime',
          'subType' => NULL,
          'dataType' => 'DateTime',
          'readonly' => NULL,
          'required' => NULL,
        ),
        'objects' =>
        array (
          'default' => NULL,
          'actualType' => 'collection',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
          'dataType' => 'array of objects (Test)',
          'children' =>
          array (
            'a' =>
            array (
              'default' => 'nelmio',
              'actualType' => 'string',
              'subType' => NULL,
              'format' => '{length: min: foo}, {not blank}',
              'required' => true,
              'dataType' => 'string',
              'readonly' => NULL,
            ),
            'b' =>
            array (
              'default' => NULL,
              'actualType' => 'datetime',
              'subType' => NULL,
              'dataType' => 'DateTime',
              'readonly' => NULL,
              'required' => NULL,
            ),
          ),
          'readonly' => NULL,
          'required' => NULL,
        ),
        'number' =>
        array (
          'dataType' => 'DateTime',
          'actualType' => 'datetime',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'related' =>
        array (
          'dataType' => 'object (Test)',
          'actualType' => 'model',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
          'children' =>
          array (
            'a' =>
            array (
              'default' => 'nelmio',
              'actualType' => 'string',
              'subType' => NULL,
              'format' => '{length: min: foo}, {not blank}',
              'required' => true,
              'dataType' => 'string',
              'readonly' => NULL,
            ),
            'b' =>
            array (
              'default' => NULL,
              'actualType' => 'datetime',
              'subType' => NULL,
              'dataType' => 'DateTime',
              'readonly' => NULL,
              'required' => NULL,
            ),
          ),
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    18 =>
    array (
      'method' => 'ANY',
      'uri' => '/z-return-selected-parsers-input',
      'parameters' =>
      array (
        'a' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => NULL,
          'required' => true,
          'description' => 'A nice description',
          'readonly' => false,
        ),
        'b' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => NULL,
          'required' => false,
          'description' => NULL,
          'readonly' => false,
        ),
        'c' =>
        array (
          'dataType' => 'boolean',
          'actualType' => 'boolean',
          'subType' => NULL,
          'default' => false,
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
        'd' =>
        array (
          'dataType' => 'string',
          'actualType' => 'string',
          'subType' => NULL,
          'default' => 'DefaultTest',
          'required' => true,
          'description' => NULL,
          'readonly' => false,
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    19 =>
    array (
      'method' => 'ANY',
      'uri' => '/z-return-selected-parsers-output',
      'response' =>
      array (
        'bar' =>
        array (
          'default' => NULL,
          'actualType' => 'datetime',
          'subType' => NULL,
          'dataType' => 'DateTime',
          'readonly' => NULL,
          'required' => NULL,
        ),
        'objects' =>
        array (
          'default' => NULL,
          'actualType' => 'collection',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
          'dataType' => 'array of objects (Test)',
          'children' =>
          array (
            'a' =>
            array (
              'default' => 'nelmio',
              'actualType' => 'string',
              'subType' => NULL,
              'format' => '{length: min: foo}, {not blank}',
              'required' => true,
              'dataType' => 'string',
              'readonly' => NULL,
            ),
            'b' =>
            array (
              'default' => NULL,
              'actualType' => 'datetime',
              'subType' => NULL,
              'dataType' => 'DateTime',
              'readonly' => NULL,
              'required' => NULL,
            ),
          ),
          'readonly' => NULL,
          'required' => NULL,
        ),
        'number' =>
        array (
          'dataType' => 'DateTime',
          'actualType' => 'datetime',
          'subType' => NULL,
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
        ),
        'related' =>
        array (
          'dataType' => 'object (Test)',
          'actualType' => 'model',
          'subType' => 'Nelmio\\ApiDocBundle\\Tests\\Fixtures\\Model\\Test',
          'required' => false,
          'default' => NULL,
          'description' => '',
          'readonly' => false,
          'sinceVersion' => NULL,
          'untilVersion' => NULL,
          'children' =>
          array (
            'a' =>
            array (
              'default' => 'nelmio',
              'actualType' => 'string',
              'subType' => NULL,
              'format' => '{length: min: foo}, {not blank}',
              'required' => true,
              'dataType' => 'string',
              'readonly' => NULL,
            ),
            'b' =>
            array (
              'default' => NULL,
              'actualType' => 'datetime',
              'subType' => NULL,
              'dataType' => 'DateTime',
              'readonly' => NULL,
              'required' => NULL,
            ),
          ),
        ),
      ),
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    20 =>
    array (
      'method' => 'POST',
      'uri' => '/zcached',
      'cache' => 60,
      'https' => false,
      'authentication' => false,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
    21 =>
    array (
      'method' => 'POST',
      'uri' => '/zsecured',
      'https' => false,
      'authentication' => true,
      'authenticationRoles' =>
      array (
      ),
      'deprecated' => false,
    ),
  ),
);
