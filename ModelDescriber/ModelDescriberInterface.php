<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use Nelmio\ApiDocBundle\Model\ModelOptions;
use EXSyst\Component\Swagger\Schema;

interface ModelDescriberInterface
{
    public function describe(Schema $schema, ModelOptions $options);

    public function supports(ModelOptions $options);
}
