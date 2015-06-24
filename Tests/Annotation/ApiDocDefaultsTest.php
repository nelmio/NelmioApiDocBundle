<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Annotation;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Annotation\ApiDocDefaults;
use Nelmio\ApiDocBundle\Tests\TestCase;

class ApiDocDefaultsTest extends TestCase
{
    public function testConstructorWithoutParams()
    {
        $annotation = new ApiDocDefaults(array());

        $this->assertEmpty($annotation->getSection());
    }

    public function testConstructorWithSection()
    {
        $section = 'Blah!';

        $annotation = new ApiDocDefaults(array(
            'section' => $section,
        ));

        $this->assertSame($section, $annotation->getSection());
    }

    public function testSectionGetterAndSetter()
    {
        $section = 'Bar!';

        $annotation = new ApiDocDefaults(array());
        $annotation->setSection($section);

        $this->assertSame($section, $annotation->getSection());
    }

    public function testToArrayWithoutDataReturnsEmptyArray()
    {
        $annotation = new ApiDocDefaults(array());

        $this->assertSame(array(), $annotation->toArray());
    }

    public function testToArrayWithDataReturnsFilledArray()
    {
        $section = 'Foo';

        $annotiation = new ApiDocDefaults(array());

        $annotiation->setSection($section);

        $this->assertSame(
            array(
                'section' => $section,
            ),
            $annotiation->toArray()
        );
    }
}
