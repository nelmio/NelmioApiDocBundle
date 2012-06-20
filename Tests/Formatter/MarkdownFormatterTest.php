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

### `GET` /tests ###

_index action_

#### Filters ####

a:

  * dataType: integer

b:

  * dataType: string
  * arbitrary: ["arg1","arg2"]


### `GET` /tests ###

_index action_

#### Filters ####

a:

  * dataType: integer

b:

  * dataType: string
  * arbitrary: ["arg1","arg2"]


### `POST` /tests ###

_create test_

#### Parameters ####

c:

  * type: boolean
  * required: true

a:

  * type: string
  * required: true
  * description: A nice description

b:

  * type: string
  * required: false


### `POST` /tests ###

_create test_

#### Parameters ####

c:

  * type: boolean
  * required: true

a:

  * type: string
  * required: true
  * description: A nice description

b:

  * type: string
  * required: false



# others #

### `ANY` /any ###

_Action without HTTP verb_


### `ANY` /any/{foo} ###

_Action without HTTP verb_

#### Requirements ####

**foo**



### `ANY` /my-commented/{id}/{page} ###

_This method is useful to test if the getDocComment works. And, it supports multilines until the first '@' char._

#### Requirements ####

**id**

  - Type: int
  - Description: A nice comment
**page**

  - Type: int


### `ANY` /yet-another/{id} ###


#### Requirements ####

**id**

  - Value: \d+
MARKDOWN;

        $this->assertEquals($expected, $result);
    }

    public function testFormatOne()
    {
        $container = $this->getContainer();

        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data      = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::indexAction', 'test_route_1');
        $result    = $container->get('nelmio_api_doc.formatter.markdown_formatter')->formatOne($data['annotation'], $data['route']);

        $expected = <<<MARKDOWN
### `GET` /tests ###

_index action_

#### Filters ####

a:

  * dataType: integer

b:

  * dataType: string
  * arbitrary: ["arg1","arg2"]


MARKDOWN;

        $this->assertEquals($expected, $result);
    }
}
