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

        if (class_exists('Dunglas\ApiBundle\DunglasApiBundle')) {
$expected = <<<MARKDOWN
# Blah #

### `ANY` /inherticance_own_attributes ###

_index action_



# FooSection #

### `ANY` /inherticance ###

_index action_



# Popo #

### `GET` /popos ###

_Retrieves the collection of Popo resources._

#### Response ####

foo:

  * type: string


### `POST` /popos ###

_Creates a Popo resource._

#### Parameters ####

foo:

  * type: string
  * required: false

#### Response ####

foo:

  * type: string


### `GET` /popos/{id} ###

_Retrieves Popo resource._

#### Requirements ####

**id**

  - Type: int

#### Response ####

foo:

  * type: string


### `PUT` /popos/{id} ###

_Replaces the Popo resource._

#### Requirements ####

**id**

  - Type: string

#### Parameters ####

foo:

  * type: string
  * required: false

#### Response ####

foo:

  * type: string


### `DELETE` /popos/{id} ###

_Deletes the Popo resource._

#### Requirements ####

**id**

  - Type: string



## /api/other-resources ##

### `GET` /api/other-resources.{_format} ###

_List another resource._

#### Requirements ####

**_format**

  - Requirement: json|xml|html

#### Response ####

[]:

  * type: array of objects (JmsTest)

[][foo]:

  * type: string

[][bar]:

  * type: DateTime

[][number]:

  * type: double

[][arr]:

  * type: array

[][nested]:

  * type: object (JmsNested)

[][nested][foo]:

  * type: DateTime

[][nested][bar]:

  * type: string

[][nested][baz][]:

  * type: array of integers
  * description: Epic description.

With multiple lines.

[][nested][circular]:

  * type: object (JmsNested)

[][nested][parent]:

  * type: object (JmsTest)

[][nested][parent][foo]:

  * type: string

[][nested][parent][bar]:

  * type: DateTime

[][nested][parent][number]:

  * type: double

[][nested][parent][arr]:

  * type: array

[][nested][parent][nested]:

  * type: object (JmsNested)

[][nested][parent][nested_array][]:

  * type: array of objects (JmsNested)

[][nested][since]:

  * type: string
  * versions: >=0.2

[][nested][until]:

  * type: string
  * versions: <=0.3

[][nested][since_and_until]:

  * type: string
  * versions: >=0.4,<=0.5

[][nested_array][]:

  * type: array of objects (JmsNested)


### `PUT|PATCH` /api/other-resources/{id}.{_format} ###

_Update a resource bu ID._

#### Requirements ####

**_format**

  - Requirement: json|xml|html
**id**



## /api/resources ##

### `GET` /api/resources.{_format} ###

_List resources._

#### Requirements ####

**_format**

  - Requirement: json|xml|html

#### Response ####

tests[]:

  * type: array of objects (Test)

tests[][a]:

  * type: string

tests[][b]:

  * type: DateTime


### `POST` /api/resources.{_format} ###

_Create a new resource._

#### Requirements ####

**_format**

  - Requirement: json|xml|html

#### Parameters ####

a:

  * type: string
  * required: true
  * description: Something that describes A.

b:

  * type: float
  * required: true

c:

  * type: choice
  * required: true

d:

  * type: datetime
  * required: true

e:

  * type: date
  * required: true

g:

  * type: string
  * required: true

#### Response ####

foo:

  * type: DateTime

bar:

  * type: string

baz[]:

  * type: array of integers
  * description: Epic description.

With multiple lines.

circular:

  * type: object (JmsNested)

circular[foo]:

  * type: DateTime

circular[bar]:

  * type: string

circular[baz][]:

  * type: array of integers
  * description: Epic description.

With multiple lines.

circular[circular]:

  * type: object (JmsNested)

circular[parent]:

  * type: object (JmsTest)

circular[parent][foo]:

  * type: string

circular[parent][bar]:

  * type: DateTime

circular[parent][number]:

  * type: double

circular[parent][arr]:

  * type: array

circular[parent][nested]:

  * type: object (JmsNested)

circular[parent][nested_array][]:

  * type: array of objects (JmsNested)

circular[since]:

  * type: string
  * versions: >=0.2

circular[until]:

  * type: string
  * versions: <=0.3

circular[since_and_until]:

  * type: string
  * versions: >=0.4,<=0.5

parent:

  * type: object (JmsTest)

parent[foo]:

  * type: string

parent[bar]:

  * type: DateTime

parent[number]:

  * type: double

parent[arr]:

  * type: array

parent[nested]:

  * type: object (JmsNested)

parent[nested_array][]:

  * type: array of objects (JmsNested)

since:

  * type: string
  * versions: >=0.2

until:

  * type: string
  * versions: <=0.3

since_and_until:

  * type: string
  * versions: >=0.4,<=0.5


### `GET` /api/resources/{id}.{_format} ###

_Retrieve a resource by ID._

#### Requirements ####

**_format**

  - Requirement: json|xml|html
**id**



### `DELETE` /api/resources/{id}.{_format} ###

_Delete a resource by ID._

#### Requirements ####

**_format**

  - Requirement: json|xml|html
**id**



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

d:

  * type: string
  * required: true
  * default value: DefaultTest


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

d:

  * type: string
  * required: true
  * default value: DefaultTest


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

dependency_type:

  * type: object (dependency_type)
  * required: true

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
  * default value: baz

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

dependency_type:

  * type: object (dependency_type)

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

  - Requirement: \d+


### `GET` /z-action-with-deprecated-indicator ###
### This method is deprecated ###




### `POST` /z-action-with-nullable-request-param ###


#### Parameters ####

param1:

  * type: string
  * required: false
  * description: Param1 description.


### `GET` /z-action-with-query-param ###


#### Filters ####

page:

  * Requirement: \d+
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

related:

  * type: object (Test)

related[a]:

  * type: string

related[b]:

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

d:

  * type: string
  * required: true
  * default value: DefaultTest


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

related:

  * type: object (Test)

related[a]:

  * type: string

related[b]:

  * type: DateTime


### `POST` /zcached ###



### `POST` /zsecured ###
MARKDOWN;
        } else {
            $expected = <<<MARKDOWN
## /api/other-resources ##

### `GET` /api/other-resources.{_format} ###

_List another resource._

#### Requirements ####

**_format**

  - Requirement: json|xml|html

#### Response ####

[]:

  * type: array of objects (JmsTest)

[][foo]:

  * type: string

[][bar]:

  * type: DateTime

[][number]:

  * type: double

[][arr]:

  * type: array

[][nested]:

  * type: object (JmsNested)

[][nested][foo]:

  * type: DateTime

[][nested][bar]:

  * type: string

[][nested][baz][]:

  * type: array of integers
  * description: Epic description.

With multiple lines.

[][nested][circular]:

  * type: object (JmsNested)

[][nested][parent]:

  * type: object (JmsTest)

[][nested][parent][foo]:

  * type: string

[][nested][parent][bar]:

  * type: DateTime

[][nested][parent][number]:

  * type: double

[][nested][parent][arr]:

  * type: array

[][nested][parent][nested]:

  * type: object (JmsNested)

[][nested][parent][nested_array][]:

  * type: array of objects (JmsNested)

[][nested][since]:

  * type: string
  * versions: >=0.2

[][nested][until]:

  * type: string
  * versions: <=0.3

[][nested][since_and_until]:

  * type: string
  * versions: >=0.4,<=0.5

[][nested_array][]:

  * type: array of objects (JmsNested)


### `PUT|PATCH` /api/other-resources/{id}.{_format} ###

_Update a resource bu ID._

#### Requirements ####

**_format**

  - Requirement: json|xml|html
**id**



## /api/resources ##

### `GET` /api/resources.{_format} ###

_List resources._

#### Requirements ####

**_format**

  - Requirement: json|xml|html

#### Response ####

tests[]:

  * type: array of objects (Test)

tests[][a]:

  * type: string

tests[][b]:

  * type: DateTime


### `POST` /api/resources.{_format} ###

_Create a new resource._

#### Requirements ####

**_format**

  - Requirement: json|xml|html

#### Parameters ####

a:

  * type: string
  * required: true
  * description: Something that describes A.

b:

  * type: float
  * required: true

c:

  * type: choice
  * required: true

d:

  * type: datetime
  * required: true

e:

  * type: date
  * required: true

g:

  * type: string
  * required: true

#### Response ####

foo:

  * type: DateTime

bar:

  * type: string

baz[]:

  * type: array of integers
  * description: Epic description.

With multiple lines.

circular:

  * type: object (JmsNested)

circular[foo]:

  * type: DateTime

circular[bar]:

  * type: string

circular[baz][]:

  * type: array of integers
  * description: Epic description.

With multiple lines.

circular[circular]:

  * type: object (JmsNested)

circular[parent]:

  * type: object (JmsTest)

circular[parent][foo]:

  * type: string

circular[parent][bar]:

  * type: DateTime

circular[parent][number]:

  * type: double

circular[parent][arr]:

  * type: array

circular[parent][nested]:

  * type: object (JmsNested)

circular[parent][nested_array][]:

  * type: array of objects (JmsNested)

circular[since]:

  * type: string
  * versions: >=0.2

circular[until]:

  * type: string
  * versions: <=0.3

circular[since_and_until]:

  * type: string
  * versions: >=0.4,<=0.5

parent:

  * type: object (JmsTest)

parent[foo]:

  * type: string

parent[bar]:

  * type: DateTime

parent[number]:

  * type: double

parent[arr]:

  * type: array

parent[nested]:

  * type: object (JmsNested)

parent[nested_array][]:

  * type: array of objects (JmsNested)

since:

  * type: string
  * versions: >=0.2

until:

  * type: string
  * versions: <=0.3

since_and_until:

  * type: string
  * versions: >=0.4,<=0.5


### `GET` /api/resources/{id}.{_format} ###

_Retrieve a resource by ID._

#### Requirements ####

**_format**

  - Requirement: json|xml|html
**id**



### `DELETE` /api/resources/{id}.{_format} ###

_Delete a resource by ID._

#### Requirements ####

**_format**

  - Requirement: json|xml|html
**id**



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

d:

  * type: string
  * required: true
  * default value: DefaultTest


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

d:

  * type: string
  * required: true
  * default value: DefaultTest


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

dependency_type:

  * type: object (dependency_type)
  * required: true

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
  * default value: baz

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

dependency_type:

  * type: object (dependency_type)

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

  - Requirement: \d+


### `GET` /z-action-with-deprecated-indicator ###
### This method is deprecated ###




### `POST` /z-action-with-nullable-request-param ###


#### Parameters ####

param1:

  * type: string
  * required: false
  * description: Param1 description.


### `GET` /z-action-with-query-param ###


#### Filters ####

page:

  * Requirement: \d+
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

related:

  * type: object (Test)

related[a]:

  * type: string

related[b]:

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

d:

  * type: string
  * required: true
  * default value: DefaultTest


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

related:

  * type: object (Test)

related[a]:

  * type: string

related[b]:

  * type: DateTime


### `POST` /zcached ###



### `POST` /zsecured ###
MARKDOWN;
        }

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
