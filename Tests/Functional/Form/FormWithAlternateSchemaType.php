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
use OpenApi\Attributes as OAT;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

if (PHP_VERSION_ID < 80100) {
    /**
     * @OA\Schema(type="string")
     */
    class FormWithAlternateSchemaType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('ignored', TextType::class, [
                    'required' => false,
                ]);
        }
    }
} else {
    #[OAT\Schema(type: 'string')]
    class FormWithAlternateSchemaType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('ignored', TextType::class, [
                    'required' => false,
                ]);
        }
    }
}
