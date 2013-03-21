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
### This method is deprecated ###


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


### `POST` /another-post ###
### This method is deprecated ###


_create another test_

#### Parameters ####

a:

  * type: string
  * required: true
  * description: A nice description


### `ANY` /any ###

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
  * description: No description.

number:

  * type: double
  * required: false
  * description: No description.

arr:

  * type: array
  * required: false
  * description: No description.

nested:

  * type: object (JmsNested)
  * required: false
  * description: No description.

nested[bar]:

  * type: string
  * required: false
  * description: No description.

nested[baz][]:

  * type: array of integers
  * required: false
  * description: Epic description.

With multiple lines.

nested[circular]:

  * type: object (JmsNested)
  * required: false
  * description: No description.

nested[parent]:

  * type: object (JmsTest)
  * required: false
  * description: No description.

nested[parent][foo]:

  * type: string
  * required: false
  * description: No description.

nested[parent][number]:

  * type: double
  * required: false
  * description: No description.

nested[parent][arr]:

  * type: array
  * required: false
  * description: No description.

nested[parent][nested]:

  * type: object (JmsNested)
  * required: false
  * description: No description.

nested[parent][nestedArray][]:

  * type: array of objects (JmsNested)
  * required: false
  * description: No description.

nestedArray[]:

  * type: array of objects (JmsNested)
  * required: false
  * description: No description.


### `GET` /jms-return-test ###
### This method is deprecated ###


_Testing return_

#### Response ####

a:

  * type: string
  * description: A nice description


### `ANY` /my-commented/{id}/{page} ###
### This method is deprecated ###

_This method is useful to test if the getDocComment works._

#### Requirements ####

**id**

  - Type: int
  - Description: A nice comment
**page**

  - Type: int


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
