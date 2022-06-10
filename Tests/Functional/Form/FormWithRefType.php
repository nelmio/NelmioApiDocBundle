<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Form;

use OpenApi\Annotations as OA;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\InputType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @OA\Schema(ref="#/components/schemas/Test")
 */
class FormWithRefType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ignored', InputType::class, [
                'required' => false,
            ]);
    }
}
