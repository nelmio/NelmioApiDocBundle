<?php

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class JMSNamingStrategyConstraints
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("beautifulName")
     *
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="\w+")
     * @Assert\Length(min="3", max="10")
     */
    private $some_weird_named_property = 'default';

    public function getSomeWeirdNamedProperty(): string
    {
        return $this->some_weird_named_property;
    }

    public function setSomeWeirdNamedProperty(string $some_weird_named_property)
    {
        $this->some_weird_named_property = $some_weird_named_property;
    }
}
