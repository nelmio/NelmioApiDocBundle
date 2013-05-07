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
### This method is deprecated ###


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
### This method is deprecated ###


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
### This method is deprecated ###


_create test_

#### Requirements ####

**_format**


#### Parameters ####

test_type[a]:

  * type: string
  * required: true
  * description: A nice description

test_type[b]:

  * type: string
  * required: false

test_type[c]:

  * type: boolean
  * required: true


### `POST` /tests.{_format} ###
### This method is deprecated ###


_create test_

#### Requirements ####

**_format**


#### Parameters ####

test_type[a]:

  * type: string
  * required: true
  * description: A nice description

test_type[b]:

  * type: string
  * required: false

test_type[c]:

  * type: boolean
  * required: true


## /tests2 ##

### `POST` /tests2.{_format} ###
### This method is deprecated ###


_post test 2_

#### Requirements ####

**_format**



### `POST` /another-post ###
### This method is deprecated ###


_create another test_

#### Parameters ####

dependency_type[a]:

  * type: string
  * required: true
  * description: A nice description


### `ANY` /any ###
### This method is deprecated ###


_Action without HTTP verb_


### `ANY` /any/{foo} ###
### This method is deprecated ###


_Action without HTTP verb_

#### Requirements ####

**foo**



### `ANY` /authenticated ###
### This method is deprecated ###




### `POST` /jms-input-test ###
### This method is deprecated ###


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

nested_array[]:

  * type: array of objects (JmsNested)
  * required: false


### `GET` /jms-return-test ###
### This method is deprecated ###


_Testing return_

#### Response ####

dependency_type[a]:

  * type: string
  * description: A nice description


### `ANY` /my-commented/{id}/{page}/{paramType}/{param} ###
### This method is deprecated ###


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


### `ANY` /secure-route ###
### This method is deprecated ###



#### Requirements ####

**_scheme**

  - Requirement: https


### `ANY` /yet-another/{id} ###
### This method is deprecated ###



#### Requirements ####

**id**

  - Requirement: \\d+


### `GET` /z-action-with-query-param ###
### This method is deprecated ###



#### Filters ####

page:

  * Requirement: \\d+
  * Description: Page of the overview.
  * Default: 1


### `GET` /z-action-with-query-param-no-default ###
### This method is deprecated ###



#### Filters ####

page:

  * Requirement: \d+
  * Description: Page of the overview.


### `GET` /z-action-with-query-param-strict ###
### This method is deprecated ###



#### Requirements ####

**page**

  - Requirement: \d+
  - Description: Page of the overview.


### `POST` /z-action-with-request-param ###
### This method is deprecated ###



#### Parameters ####

param1:

  * type: string
  * required: true
  * description: Param1 description.
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
### This method is deprecated ###


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
