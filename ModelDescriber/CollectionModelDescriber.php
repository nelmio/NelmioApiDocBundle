<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use EXSyst\Component\Swagger\Schema;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\ModelOptions;

class CollectionModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(Schema $schema, ModelOptions $options)
    {
        $schema->setType('array');
        $this->modelRegistry->register($schema->getItems())
            ->setType($options->getType()->getCollectionValueType());
    }

    public function supports(ModelOptions $options)
    {
        return $options->getType()->isCollection() && null !== $options->getType()->getCollectionValueType();
    }
}
