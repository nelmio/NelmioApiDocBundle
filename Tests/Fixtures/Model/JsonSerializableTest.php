<?php
/**
 * Created by mcfedr on 30/06/15 21:05
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

class JsonSerializableTest implements \JsonSerializable
{
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array(
            'id' => 123,
            'name' => 'My name',
            'child' => array(
                'value' => array(1, 2, 3)
            ),
            'another' => new JsonSerializableOptionalConstructorTest()
        );
    }
}
