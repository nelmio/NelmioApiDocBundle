<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $container->services()

       ->set('nelmio_api_doc.command.dump', \Nelmio\ApiDocBundle\Command\DumpCommand::class)
           ->public()
           ->args([service('nelmio_api_doc.render_docs')])
           ->tag('console.command')

       ->set('nelmio_api_doc.controller.swagger_ui', \Nelmio\ApiDocBundle\Controller\SwaggerUiController::class)
           ->public()
           ->args([
               service('nelmio_api_doc.render_docs'),
               'swaggerui',
           ])

       ->set('nelmio_api_doc.controller.redocly', \Nelmio\ApiDocBundle\Controller\SwaggerUiController::class)
           ->public()
           ->args([
               service('nelmio_api_doc.render_docs'),
               'redocly',
           ])

       ->set('nelmio_api_doc.controller.stoplight', \Nelmio\ApiDocBundle\Controller\SwaggerUiController::class)
           ->public()
           ->args([
               service('nelmio_api_doc.render_docs'),
               'stoplight',
           ])

       ->alias('nelmio_api_doc.controller.swagger', 'nelmio_api_doc.controller.swagger_json')
           ->public()

       ->set('nelmio_api_doc.controller.swagger_json', \Nelmio\ApiDocBundle\Controller\DocumentationController::class)
           ->public()
           ->args([service('nelmio_api_doc.render_docs')])

       ->set('nelmio_api_doc.controller.swagger_yaml', \Nelmio\ApiDocBundle\Controller\YamlDocumentationController::class)
           ->public()
           ->args([service('nelmio_api_doc.render_docs')])

       ->set('nelmio_api_doc.render_docs', \Nelmio\ApiDocBundle\Render\RenderOpenApi::class)
           ->public()
           ->args([
               service('nelmio_api_doc.generator_locator'),
               service('nelmio_api_doc.render_docs.json'),
               service('nelmio_api_doc.render_docs.yaml'),
               service('nelmio_api_doc.render_docs.html')->ignoreOnInvalid(),
           ])

       ->set('nelmio_api_doc.render_docs.html', \Nelmio\ApiDocBundle\Render\Html\HtmlOpenApiRenderer::class)
           ->private()
           ->args([
               service('twig'),
               [],
           ])

       ->set('nelmio_api_doc.render_docs.html.asset', \Nelmio\ApiDocBundle\Render\Html\GetNelmioAsset::class)
           ->private()
           ->args([service('twig.extension.assets')])
           ->tag('twig.extension')

       ->set('nelmio_api_doc.render_docs.json', \Nelmio\ApiDocBundle\Render\Json\JsonOpenApiRenderer::class)
           ->private()

       ->set('nelmio_api_doc.render_docs.yaml', \Nelmio\ApiDocBundle\Render\Yaml\YamlOpenApiRenderer::class)
           ->private()

       ->alias('nelmio_api_doc.generator', 'nelmio_api_doc.generator.default')
           ->public()

       ->set('nelmio_api_doc.controller_reflector', \Nelmio\ApiDocBundle\Util\ControllerReflector::class)
           ->private()
           ->args([service('service_container')])

       ->set('nelmio_api_doc.describers.config', \Nelmio\ApiDocBundle\Describer\ExternalDocDescriber::class)
           ->private()
           ->args([[]])
           ->tag('nelmio_api_doc.describer', ['priority' => 1000])

       ->set('nelmio_api_doc.describers.default', \Nelmio\ApiDocBundle\Describer\DefaultDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.describer', ['priority' => -1000])

       ->set('nelmio_api_doc.route_describers.route_metadata', \Nelmio\ApiDocBundle\RouteDescriber\RouteMetadataDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.route_describer', ['priority' => -300])

       ->set('nelmio_api_doc.model_describers.self_describing', \Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.model_describer', ['priority' => 1000])

       ->set('nelmio_api_doc.model_describers.object', \Nelmio\ApiDocBundle\ModelDescriber\ObjectModelDescriber::class)
           ->private()
           ->args([
               service('property_info'),
               service('nelmio_api_doc.object_model.property_describer'),
               abstract_arg('should be set to the configured media types'),
               service('serializer.name_converter.metadata_aware')->ignoreOnInvalid(),
               '%nelmio_api_doc.use_validation_groups%',
               service('serializer.mapping.class_metadata_factory')->ignoreOnInvalid(),
           ])
           ->tag('nelmio_api_doc.model_describer')

       ->set('nelmio_api_doc.model_describers.enum', \Nelmio\ApiDocBundle\ModelDescriber\EnumModelDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.model_describer', ['priority' => 100])

       ->set('nelmio_api_doc.model_describers.object_fallback', \Nelmio\ApiDocBundle\ModelDescriber\FallbackObjectModelDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.model_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describer', \Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriber::class)
           ->private()
           ->args([tagged_iterator('nelmio_api_doc.object_model.property_describer')])
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => 100])

       ->set('nelmio_api_doc.object_model.property_describers.array', \Nelmio\ApiDocBundle\PropertyDescriber\ArrayPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.dictionary', \Nelmio\ApiDocBundle\PropertyDescriber\DictionaryPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.boolean', \Nelmio\ApiDocBundle\PropertyDescriber\BooleanPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.float', \Nelmio\ApiDocBundle\PropertyDescriber\FloatPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.integer', \Nelmio\ApiDocBundle\PropertyDescriber\IntegerPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.string', \Nelmio\ApiDocBundle\PropertyDescriber\StringPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.date_time', \Nelmio\ApiDocBundle\PropertyDescriber\DateTimePropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.nullable', \Nelmio\ApiDocBundle\PropertyDescriber\NullablePropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer')

       ->set('nelmio_api_doc.object_model.property_describers.object', \Nelmio\ApiDocBundle\PropertyDescriber\ObjectPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.compound', \Nelmio\ApiDocBundle\PropertyDescriber\CompoundPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.object_model.property_describers.uuid', \Nelmio\ApiDocBundle\PropertyDescriber\UuidPropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer')

       ->set('nelmio_api_doc.object_model.property_describers.translatable', \Nelmio\ApiDocBundle\PropertyDescriber\TranslatablePropertyDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.object_model.property_describer')

       ->set('nelmio_api_doc.form.documentation_extension', \Nelmio\ApiDocBundle\Form\Extension\DocumentationExtension::class)
           ->tag('form.type_extension', ['extended_type' => \Symfony\Component\Form\Extension\Core\Type\FormType::class])

       ->set('nelmio_api_doc.swagger.processor.nullable_property', \Nelmio\ApiDocBundle\Processor\NullablePropertyProcessor::class)
           ->tag('nelmio_api_doc.swagger.processor')

       ->set('nelmio_api_doc.type_describer.chain', \Nelmio\ApiDocBundle\TypeDescriber\ChainDescriber::class)
           ->private()
           ->args([tagged_iterator('nelmio_api_doc.type_describer')])
           ->tag('nelmio_api_doc.type_describer', ['priority' => 1000])

       ->set('nelmio_api_doc.type_describer.array', \Nelmio\ApiDocBundle\TypeDescriber\ArrayDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.bool', \Nelmio\ApiDocBundle\TypeDescriber\BoolDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.class', \Nelmio\ApiDocBundle\TypeDescriber\ClassDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.dictionary', \Nelmio\ApiDocBundle\TypeDescriber\DictionaryDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.float', \Nelmio\ApiDocBundle\TypeDescriber\FloatDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.generic_class', \Nelmio\ApiDocBundle\TypeDescriber\GenericClassDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.generic_collection', \Nelmio\ApiDocBundle\TypeDescriber\GenericCollectionDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.integer', \Nelmio\ApiDocBundle\TypeDescriber\IntegerDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.intersection', \Nelmio\ApiDocBundle\TypeDescriber\IntersectionDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.list', \Nelmio\ApiDocBundle\TypeDescriber\ListDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.mixed', \Nelmio\ApiDocBundle\TypeDescriber\MixedDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.nullable', \Nelmio\ApiDocBundle\TypeDescriber\NullableDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -950])

       ->set('nelmio_api_doc.type_describer.object', \Nelmio\ApiDocBundle\TypeDescriber\ObjectDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.string', \Nelmio\ApiDocBundle\TypeDescriber\StringDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.template', \Nelmio\ApiDocBundle\TypeDescriber\TemplateDescriber::class)
           ->private()
           ->args([service('nelmio_api_doc.type_describer.chain')])
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])

       ->set('nelmio_api_doc.type_describer.union', \Nelmio\ApiDocBundle\TypeDescriber\UnionDescriber::class)
           ->private()
           ->tag('nelmio_api_doc.type_describer', ['priority' => -1000])
    ;
};
