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

a:

  * type: string
  * required: true
  * description: A nice description

b:

  * type: string
  * required: true


### `POST` /tests ###

_create test_

#### Parameters ####

a:

  * type: string
  * required: true
  * description: A nice description

b:

  * type: string
  * required: true



# others #

### `ANY` /any ###

_Action without HTTP verb_


### `ANY` /any ###

_Action without HTTP verb_


### `ANY` /my-commented ###

_This method is useful to test if the getDocComment works._
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
