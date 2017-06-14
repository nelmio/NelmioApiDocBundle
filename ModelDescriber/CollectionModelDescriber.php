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
use Nelmio\ApiDocBundle\Model\Model;

class CollectionModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(Model $model, Schema $schema)
    {
        $schema->setType('array');
        $schema->getItems()->setRef(
            $this->modelRegistry->register(new Model($model->getType()->getCollectionValueType(), $model->getGroups()))
        );
    }

    public function supports(Model $model): bool
    {
        return $model->getType()->isCollection() && null !== $model->getType()->getCollectionValueType();
    }
}
