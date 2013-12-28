<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Formatter;

use Nelmio\ApiDocBundle\Tests\WebTestCase;

class MarkdownFormatterTest extends WebTestCase
{
    public function testFormat()
    {
        $container = $this->getContainer();

        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        set_error_handler(array($this, 'handleDeprecation'));
        $data = $extractor->all();
        restore_error_handler();
        $result = $container->get('nelmio_api_doc.formatter.markdown_formatter')->format($data);

        $expected = <<<MARKDOWN
## /tests ##

### `GET` /tests.{_format} ###

_index action_

#### Requirements ####

**_format**


#### Filters ####

a:

  * DataType: integer

b:

  * DataType: string
  * Arbitrary: ["arg1","arg2"]


### `GET` /tests.{_format} ###

_index action_

#### Requirements ####

**_format**


#### Filters ####

a:

  * DataType: integer

b:

  * DataType: string
  * Arbitrary: ["arg1","arg2"]


### `POST` /tests.{_format} ###

_create test_

#### Requirements ####

**_format**


#### Parameters ####

a:

  * type: string
  * required: true
  * description: A nice description

b:

  * type: string
  * required: false

c:

  * type: boolean
  * required: true


### `POST` /tests.{_format} ###

_create test_

#### Requirements ####

**_format**


#### Parameters ####

a:

  * type: string
  * required: true
  * description: A nice description

b:

  * type: string
  * required: false

c:

  * type: boolean
  * required: true


## /tests2 ##

### `POST` /tests2.{_format} ###

_post test 2_

#### Requirements ####

**_format**



## TestResource ##

### `ANY` /named-resource ###



### `POST` /another-post ###

_create another test_

#### Parameters ####

dependency_type[a]:

  * type: string
  * required: true
  * description: A nice description


### `ANY` /any ###

_Action without HTTP verb_


### `ANY` /any/{foo} ###

_Action without HTTP verb_

#### Requirements ####

**foo**



### `ANY` /authenticated ###



### `POST` /jms-input-test ###

_Testing JMS_

#### Parameters ####

foo:

  * type: string
  * required: false

number:

  * type: double
  * required: false

arr:

  * type: array
  * required: false

nested:

  * type: object (JmsNested)
  * required: false

nested[bar]:

  * type: string
  * required: false

nested[baz][]:

  * type: array of integers
  * required: false
  * description: Epic description.

With multiple lines.

nested[circular]:

  * type: object (JmsNested)
  * required: false

nested[parent]:

  * type: object (JmsTest)
  * required: false

nested[parent][foo]:

  * type: string
  * required: false

nested[parent][number]:

  * type: double
  * required: false

nested[parent][arr]:

  * type: array
  * required: false

nested[parent][nested]:

  * type: object (JmsNested)
  * required: false

nested[parent][nested_array][]:

  * type: array of objects (JmsNested)
  * required: false

nested[since]:

  * type: string
  * required: false

nested[until]:

  * type: string
  * required: false

nested[since_and_until]:

  * type: string
  * required: false

nested_array[]:

  * type: array of objects (JmsNested)
  * required: false


### `GET` /jms-return-test ###

_Testing return_

#### Response ####

dependency_type[a]:

  * type: string
  * description: A nice description


### `ANY` /my-commented/{id}/{page}/{paramType}/{param} ###

_This method is useful to test if the getDocComment works._

#### Requirements ####

**id**

  - Type: int
  - Description: A nice comment
**page**

  - Type: int
**paramType**

  - Type: int
  - Description: The param type
**param**

  - Type: int
  - Description: The param id


### `ANY` /return-nested-output ###


#### Response ####

foo:

  * type: string

bar:

  * type: DateTime

number:

  * type: double

arr:

  * type: array

nested:

  * type: object (JmsNested)

nested[foo]:

  * type: DateTime

nested[bar]:

  * type: string

nested[baz][]:

  * type: array of integers
  * description: Epic description.

With multiple lines.

nested[circular]:

  * type: object (JmsNested)

nested[parent]:

  * type: object (JmsTest)

nested[parent][foo]:

  * type: string

nested[parent][bar]:

  * type: DateTime

nested[parent][number]:

  * type: double

nested[parent][arr]:

  * type: array

nested[parent][nested]:

  * type: object (JmsNested)

nested[parent][nested_array][]:

  * type: array of objects (JmsNested)

nested[since]:

  * type: string
  * versions: >=0.2

nested[until]:

  * type: string
  * versions: <=0.3

nested[since_and_until]:

  * type: string
  * versions: >=0.4,<=0.5

nested_array[]:

  * type: array of objects (JmsNested)


### `ANY` /secure-route ###



### `ANY` /yet-another/{id} ###


#### Requirements ####

**id**

  - Requirement: \\d+


### `GET` /z-action-with-deprecated-indicator ###
### This method is deprecated ###




### `GET` /z-action-with-query-param ###


#### Filters ####

page:

  * Requirement: \\d+
  * Description: Page of the overview.
  * Default: 1


### `GET` /z-action-with-query-param-no-default ###


#### Filters ####

page:

  * Requirement: \d+
  * Description: Page of the overview.


### `GET` /z-action-with-query-param-strict ###


#### Requirements ####

**page**

  - Requirement: \d+
  - Description: Page of the overview.


### `POST` /z-action-with-request-param ###


#### Parameters ####

param1:

  * type: string
  * required: true
  * description: Param1 description.


### `ANY` /z-return-jms-and-validator-output ###


#### Response ####

bar:

  * type: DateTime

objects[]:

  * type: array of objects (Test)

objects[][a]:

  * type: string

objects[][b]:

  * type: DateTime

number:

  * type: DateTime


### `ANY` /z-return-selected-parsers-input ###


#### Parameters ####

a:

  * type: string
  * required: true
  * description: A nice description

b:

  * type: string
  * required: false

c:

  * type: boolean
  * required: true


### `ANY` /z-return-selected-parsers-output ###


#### Response ####

bar:

  * type: DateTime

objects[]:

  * type: array of objects (Test)

objects[][a]:

  * type: string

objects[][b]:

  * type: DateTime

number:

  * type: DateTime
MARKDOWN;

        $this->assertEquals($expected, $result);
    }

    public function testFormatOne()
    {
        $container = $this->getContainer();

        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::indexAction', 'test_route_1');
        $result     = $container->get('nelmio_api_doc.formatter.markdown_formatter')->formatOne($annotation);

        $expected = <<<MARKDOWN
### `GET` /tests.{_format} ###

_index action_

#### Requirements ####

**_format**


#### Filters ####

a:

  * DataType: integer

b:

  * DataType: string
  * Arbitrary: ["arg1","arg2"]


MARKDOWN;

        $this->assertEquals($expected, $result);
    }
}
