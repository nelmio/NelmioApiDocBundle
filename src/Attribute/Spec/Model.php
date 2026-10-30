<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Attribute\Spec;

use OpenApi\Spec\Schema\Ref;

/**
 * A model reference for use inside swagger-php spec attributes (`OpenApi\Spec`).
 *
 * The ref is filled in from the model registry when the attributes are described.
 */
#[\Attribute(\Attribute::TARGET_ALL | \Attribute::IS_REPEATABLE)]
final class Model extends Ref
{
    /**
     * @param string[]|null         $groups
     * @param mixed[]               $options
     * @param array<string, mixed>  $serializationContext
     * @param non-empty-string|null $name                 An optional custom name for the generated schema
     */
    public function __construct(
        string $type,
        private readonly ?array $groups = null,
        private readonly array $options = [],
        private readonly array $serializationContext = [],
        private readonly ?string $name = null,
        ?string $title = null,
        ?string $description = null,
    ) {
        // `type` is the OpenAPI type on a schema, so the model's class is kept as the ref
        // until the model registry replaces it
        parent::__construct(ref: $type, title: $title, description: $description);
    }

    /**
     * @return array{type: string, groups: string[]|null, options: mixed[], serializationContext: array<string, mixed>, name: non-empty-string|null}
     */
    public function getModel(): array
    {
        return [
            'type' => (string) $this->ref,
            'groups' => $this->groups,
            'options' => $this->options,
            'serializationContext' => $this->serializationContext,
            'name' => $this->name,
        ];
    }
}
