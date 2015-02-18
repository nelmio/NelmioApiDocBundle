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
use FOS\RestBundle\Controller\Annotations\View;

class FosRestHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        foreach ($annotations as $annot) {
            if ($annot instanceof RequestParam) {
                $this->handleRequestParam($annotation, $annot);
            } elseif ($annot instanceof QueryParam) {
                $this->handleQueryParam($annotation, $annot);
            } elseif ($annot instanceof View) {
                $this->handleView($annotation, $annot);
            }
        }
    }

    /**
     * Handle RequestParam Annotation
     *
     * @param  ApiDoc       $annotation
     * @param  RequestParam $annot
     */
    private function handleRequestParam(ApiDoc $annotation, RequestParam $annot)
    {
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
    }

    /**
     * Handle QueryParam Annotation
     *
     * @param  ApiDoc     $annotation
     * @param  QueryParam $annot
     */
    private function handleQueryParam(ApiDoc $annotation, QueryParam $annot)
    {
        # requirement
        if ($annot->strict && $annot->nullable === false && $annot->default === null) {
            $annotation->addRequirement($annot->name, array(
                'requirement'   => $this->handleRequirements($annot->requirements),
                'dataType'      => '',
                'description'   => $annot->description,
            ));
            return;
        }

        # filter
        $filter = array(
            'requirement'   => $this->handleRequirements($annot->requirements),
            'description'   => $annot->description,
        );
        if ($annot->default !== null) {
            $filter['default'] = $annot->default;
        }
        $annotation->addFilter($annot->name, $filter);
    }

    /**
     * Handle View Annotation
     *
     * @param  ApiDoc $annotation
     * @param  View   $annot
     */
    private function handleView(ApiDoc $annotation, View $annot)
    {
        $output = $annotation->getOutput();
        if (is_string($output)) {
            $output = array(
                'class' => $output
            );
        }

        # no class or group defined in place
        if (!isset($output['class']) || isset($output['groups'])) {
            return;
        }

        # no groups defined
        $groups = $annot->getSerializerGroups();
        if (empty($groups)) {
            return;
        }


        $output['groups'] = $groups;
        $annotation->setOutput($output);
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
