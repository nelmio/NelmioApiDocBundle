<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Serializer\Annotation\Groups;

if (Kernel::MAJOR_VERSION < 7) {
    /**
     * @author Guilhem N. <guilhem.niot@gmail.com>
     */
    class Article
    {
        /**
         * @Groups({"light"})
         */
        public function setAuthor(User $author)
        {
        }

        public function setContent(string $content)
        {
        }
    }
} else {
    class Article
    {
        #[Groups(["light"])]
        public function setAuthor(User $author)
        {
        }

        public function setContent(string $content)
        {
        }
    }
}
