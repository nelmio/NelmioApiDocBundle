<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Formatter;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
interface ApiDocSectionInterface
{
    public function getType();

    public function getTitle();

}