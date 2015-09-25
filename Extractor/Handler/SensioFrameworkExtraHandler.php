<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor\Handler;

use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SensioFrameworkExtraHandler implements HandlerInterface
{
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        foreach ($annotations as $annot) {
            if ($annot instanceof Cache) {
                $annotation->setCache($annot->getMaxAge());
            } elseif ($annot instanceof Security) {
                $annotation->setAuthentication(true);

                // expression
                $expression = $this->handleExpression($annot->getExpression());
                $annotation->setAuthenticationExpression($expression);
            }
        }
    }

    /**
     * Handle SensioFrameworkExtraBundle security expression in order to return a more human-readable string.
     *
     * @param  mixed  $expression
     * @return string
     */
    private function handleExpression($expression)
    {
        // has_role
        $expression = preg_replace('/has_role\\((\\s*)((\'|\")(\\w+)(\'|\"))(\\s*)\\)/', '$2', $expression);

        // is_granted
        $expression = preg_replace_callback(
            '/is_granted\\((\\s*)(\\[?)(\\s*)((\'|\")(.+)(\'|\"))(\\s*)(\\]?)(\\s*)(,(\\s*)(\\w+))?\\)/',
            function ($matches) {
                $attributes = 'permission '.implode(' or ', str_getcsv($matches[4]));
                $object = (isset($matches[13])) ? ' on '.$matches[13] : '';

                return $attributes.$object;
            },
            $expression
        );

        return (string) $expression;
    }
}
