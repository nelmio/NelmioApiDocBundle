<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Parser\ParserInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;
use FSC\HateoasBundle\Metadata\MetadataFactory;
// use Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Factory\LinkFactory;
use Nelmio\ApiDocBundle\Formatter\TabularSection;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 * <service id="nelmio_api_doc.provider.link" class="Nelmio\ApiDocBundle\Extractor\LinkProvider">
 *           <argument type="service" id="fsc_hateoas.metadata.factory" />
 *           <argument type="service" id="fsc_hateoas.factory.link" />
 *           <argument type="service" id="router" />
 *           <tag name="nelmio_api_doc.provider" />
 *       </service>
 */
class LinkProvider
{
	private $metadataFactory;
	private $linkFactory;
	private $router;

    public function __construct(MetadataFactory $metadataFactory, LinkFactory $linkFactory, RouterInterface $router)
    {
    	$this->metadataFactory = $metadataFactory;
    	$this->linkFactory = $linkFactory;
    	$this->router = $router;
    }

    public function get($annotation)
    {
        $class = $annotation->getOutput();

        $classMetadata = $this->metadataFactory->getMetadataForClass($class);
        $relations = $classMetadata->getRelations();

        $tabularSection = new TabularSection(array('Relation', 'Link'));
        $tabularSection->setTitle('Links');

        foreach ($relations as $relation) {
        	$routeName = $relation->getRoute();
        	$route = $this->router->getRouteCollection()->get($routeName);

        	$tabularSection->addRow(array($relation->getRel(), $route->getPattern()));
        }

        return $tabularSection;
    }
}