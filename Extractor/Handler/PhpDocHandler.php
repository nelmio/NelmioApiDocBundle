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
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;

class PhpDocHandler implements HandlerInterface
{
    /**
     * @var DocCommentExtractor
     */
    protected $commentExtractor;

    public function __construct(DocCommentExtractor $commentExtractor)
    {
        $this->commentExtractor = $commentExtractor;
    }

    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        // description
        if (null === $annotation->getDescription()) {
            $comments = explode("\n", $annotation->getDocumentation());
            // just set the first line
            $comment = trim($comments[0]);
            $comment = preg_replace("#\n+#", ' ', $comment);
            $comment = preg_replace('#\s+#', ' ', $comment);
            $comment = preg_replace('#[_`*]+#', '', $comment);

            if ('@' !== substr($comment, 0, 1)) {
                $annotation->setDescription($comment);
            }
        }

        // requirements
        $requirements = $annotation->getRequirements();
        foreach ($route->getRequirements() as $name => $value) {
            if (!isset($requirements[$name]) && '_method' !== $name && '_scheme' !== $name) {
                $requirements[$name] = array(
                    'requirement' => $value,
                    'dataType' => '',
                    'description' => '',
                );
            }

            if ('_scheme' === $name) {
                $https = ('https' == $value);
                $annotation->setHttps($https);
            }
        }

        if (method_exists($route, 'getSchemes')) {
            $annotation->setHttps(in_array('https', $route->getSchemes()));
        }

        $paramDocs = array();
        foreach (explode("\n", $this->commentExtractor->getDocComment($method)) as $line) {
            if (preg_match('{^@param (.+)}', trim($line), $matches)) {
                $paramDocs[] = $matches[1];
            }
            if (preg_match('{^@deprecated}', trim($line))) {
                $annotation->setDeprecated(true);
            }
            if (preg_match('{^@link (.+)}', trim($line), $matches)) {
                $annotation->setLink($matches[1]);
            }
        }

        $regexp = '{(\w*) *\$%s\b *(.*)}i';
        foreach ($route->compile()->getVariables() as $var) {
            $found = false;
            foreach ($paramDocs as $paramDoc) {
                if (preg_match(sprintf($regexp, preg_quote($var)), $paramDoc, $matches)) {
                    $annotationRequirements = $annotation->getrequirements();

                    if (!isset($annotationRequirements[$var]['dataType'])) {
                        $requirements[$var]['dataType'] = isset($matches[1]) ? $matches[1] : '';
                    }

                    if (!isset($annotationRequirements[$var]['description'])) {
                        $requirements[$var]['description'] = $matches[2];
                    }

                    if (!isset($requirements[$var]['requirement']) && !isset($annotationRequirements[$var]['requirement'])) {
                        $requirements[$var]['requirement'] = '';
                    }

                    $found = true;
                    break;
                }
            }

            if (!isset($requirements[$var]) && false === $found) {
                $requirements[$var] = array('requirement' => '', 'dataType' => '', 'description' => '');
            }
        }

        $annotation->setRequirements($requirements);
    }
}
