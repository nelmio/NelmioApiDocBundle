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
        $data = $extractor->all();
        $result = $container->get('nelmio_api_doc.formatter.markdown_formatter')->format($data);

        $expected = <<<MARKDOWN
# /tests #

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



# others #

### `POST` /another-post ###

_create another test_

#### Parameters ####

a:

  * type: string
  * required: true
  * description: A nice description


### `ANY` /any ###

_Action without HTTP verb_


### `ANY` /any/{foo} ###

_Action without HTTP verb_

#### Requirements ####

**foo**



### `POST` /jms-input-test ###

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

nestedArray[]:

  * type: array of objects (JmsNested)
  * required: false
  * description: No description.

nestedArray[][bar]:

  * type: string
  * required: false
  * description: No description.

nestedArray[][baz][]:

  * type: array of integers
  * required: false
  * description: Epic description.

With multiple lines.


### `GET` /jms-return-test ###

_Testing return_

#### Response ####

a:

  * type: string
  * description: A nice description


### `ANY` /my-commented/{id}/{page} ###

_This method is useful to test if the getDocComment works._

#### Requirements ####

**id**

  - Type: int
  - Description: A nice comment
**page**

  - Type: int


### `ANY` /yet-another/{id} ###


#### Requirements ####

**id**

  - Requirement: \d+


### `GET` /z-action-with-query-param ###


#### Filters ####

page:

  * Requirement: \d+
  * Description: Page of the overview.
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
