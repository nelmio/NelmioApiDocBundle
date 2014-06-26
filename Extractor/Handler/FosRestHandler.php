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

use Nelmio\ApiDocBundle\DataTypes;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Regex;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class FosRestHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        foreach ($annotations as $annot) {
            if ($annot instanceof RequestParam) {

                $requirements = $this->handleRequirements($annot->requirements);
                $data = array(
                    'required'    => $annot->strict && $annot->nullable === false && $annot->default === null,
                    'dataType'    => $requirements,
                    'actualType'  => $this->inferType($requirements),
                    'subType'     => null,
                    'description' => $annot->description,
                    'readonly'    => false
                );
                if ($annot->strict === false) {
                    $data['default'] = $annot->default;
                }
                $annotation->addParameter($annot->name, $data);
            } elseif ($annot instanceof QueryParam) {
                if ($annot->strict && $annot->nullable === false && $annot->default === null) {
                    $annotation->addRequirement($annot->name, array(
                        'requirement'   => $this->handleRequirements($annot->requirements),
                        'dataType'      => '',
                        'description'   => $annot->description,
                    ));
                } elseif ($annot->default !== null) {
                    $annotation->addFilter($annot->name, array(
                        'requirement'   => $this->handleRequirements($annot->requirements),
                        'description'   => $annot->description,
                        'default'   => $annot->default,
                    ));
                } else {
                    $annotation->addFilter($annot->name, array(
                        'requirement'   => $this->handleRequirements($annot->requirements),
                        'description'   => $annot->description,
                    ));
                }
            }
        }
    }

    /**
     * Handle FOSRestBundle requirements in order to return a string.
     *
     * @param  mixed  $requirements
     * @return string
     */
    private function handleRequirements($requirements)
    {
        if (is_object($requirements) && $requirements instanceof Constraint) {
            if ($requirements instanceof Regex) {
                return $requirements->getHtmlPattern();
            }
            $class = get_class($requirements);

            return substr($class, strrpos($class, '\\')+1);
        }

        return (string) $requirements;
    }

    public function inferType($requirement)
    {
        if (DataTypes::isPrimitive($requirement)) {
            return $requirement;
        }

        return DataTypes::STRING;
    }
}
