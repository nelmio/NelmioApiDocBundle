<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\ArrayItems\Dictionary;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\ArrayItems\Foo;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article81;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\ArticleInterface;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\CompoundEntity;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityThroughNameConverter;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithAlternateType81;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithFalsyDefaults;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithNullableSchemaSet;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithObjectType;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithRef;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel\ArrayQueryModel;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel\FilterQueryModel;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel\PaginationQueryModel;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel\SortQueryModel;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\RangeInteger;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraints81;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraintsWithValidationGroups;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyDiscriminator81;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyDiscriminatorFileMapping;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyMapQueryString;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;
use Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\Symfony7\SerializedNameEntity;
use Nelmio\ApiDocBundle\Tests\Functional\Form\DummyType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithAlternateSchemaType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithCsrfProtectionDisabledType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithCsrfProtectionEnabledType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithModel;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithRefType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\UserType;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class ApiController81
{
    #[OA\Response(
        response: '200',
        description: 'Success',
        attachables: [
            new Model(type: Article::class, groups: ['light']),
        ],
    )]
    #[OA\Parameter(ref: '#/components/parameters/test')]
    #[Route('/article/{id}', methods: ['GET'])]
    #[OA\Parameter(name: 'Accept-Version', in: 'header', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'Application-Name', in: 'header', schema: new OA\Schema(type: 'string'))]
    public function fetchArticleAction()
    {
    }

    #[OA\Get(
        responses: [
            new OA\Response(
                response: '200',
                description: 'Success',
                attachables: [
                    new Model(type: ArticleInterface::class, groups: ['light']),
                ],
            ),
        ],
    )]
    #[OA\Parameter(ref: '#/components/parameters/test')]
    #[Route('/article-interface/{id}', methods: ['GET'])]
    #[OA\Parameter(name: 'Accept-Version', in: 'header', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'Application-Name', in: 'header', schema: new OA\Schema(type: 'string'))]
    public function fetchArticleInterfaceAction()
    {
    }

    #[Route('/swagger', methods: ['GET', 'LINK'])]
    #[Route('/swagger2', methods: ['GET'])]
    #[Operation([
        'responses' => [
            new OA\Response(
                response: '201',
                description: 'An example resource',
            ),
        ],
    ])]
    #[OA\Get(
        path: '/api/swagger2',
        parameters: [
            new OA\Parameter(name: 'Accept-Version', in: 'header', schema: new OA\Schema(type: 'string')),
        ],
    )]
    #[OA\Post(
        path: '/api/swagger2',
        responses: [
            new OA\Response(
                response: '203',
                description: 'but 203 is not actually allowed (wrong method)',
            ),
        ],
    )]
    public function swaggerAction()
    {
    }

    #[Route('/swagger/implicit', methods: ['GET', 'POST'])]
    #[OA\Response(
        response: '201',
        description: 'Operation automatically detected',
        attachables: [
            new Model(type: User::class),
        ],
    )]
    #[OA\RequestBody(
        description: 'This is a request body',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/User'),
        ),
    )]
    #[OA\Tag(name: 'implicit')]
    public function implicitSwaggerAction()
    {
    }

    #[Route('/test/users/{user}', methods: ['POST'], schemes: ['https'], requirements: ['user' => '/foo/'])]
    #[OA\Response(
        response: '201',
        description: 'Operation automatically detected',
        attachables: [
            new Model(type: User::class),
        ],
    )]
    #[OA\RequestBody(
        description: 'This is a request body',
        content: new Model(type: UserType::class, options: ['bar' => 'baz']),
    )]
    public function submitUserTypeAction()
    {
    }

    #[Route('/test/{user}', methods: ['GET'], schemes: ['https'], requirements: ['user' => '/foo/'])]
    #[OA\Response(response: 200, description: 'sucessful')]
    public function userAction()
    {
    }

    /**
     * This action is deprecated.
     *
     * Please do not use this action.
     *
     * @deprecated
     */
    #[Route('/deprecated', methods: ['GET'])]
    public function deprecatedAction()
    {
    }

    /**
     * This action is not documented. It is excluded by the config.
     */
    #[Route('/admin', methods: ['GET'])]
    public function adminAction()
    {
    }

    #[OA\Get(
        path: '/filtered',
        responses: [
            new OA\Response(response: '201', description: ''),
        ],
    )]
    public function filteredAction()
    {
    }

    #[Route('/form', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Request content',
        content: new Model(type: DummyType::class),
    )]
    #[OA\Response(response: 201, description: '')]
    public function formAction()
    {
    }

    #[Route('/form-model', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Request content',
        content: new Model(type: FormWithModel::class),
    )]
    #[OA\Response(response: 201, description: '')]
    public function formWithModelAction()
    {
    }

    #[Route('/security', methods: ['GET'])]
    #[OA\Response(response: 201, description: '')]
    #[Security(name: 'api_key')]
    #[Security(name: 'basic')]
    #[Security(name: 'oauth2', scopes: ['scope_1'])]
    public function securityAction()
    {
    }

    #[Route('/securityOverride')]
    #[OA\Response(response: 201, description: '')]
    #[Security(name: 'api_key')]
    #[Security(name: null)]
    public function securityActionOverride()
    {
    }

    #[Route('/swagger/symfonyConstraints', methods: ['GET'])]
    #[OA\Response(
        response: '201',
        description: 'Used for symfony constraints test',
        content: new Model(type: SymfonyConstraints81::class),
    )]
    public function symfonyConstraintsAction()
    {
    }

    #[OA\Response(
        response: '200',
        description: 'Success',
        ref: '#/components/schemas/Test',
    )]
    #[OA\Response(
        response: '201',
        ref: '#/components/responses/201',
    )]
    #[Route('/configReference', methods: ['GET'])]
    public function configReferenceAction()
    {
    }

    #[Route('/multi-annotations', methods: ['GET', 'POST'])]
    #[OA\Get(description: 'This is the get operation')]
    #[OA\Post(description: 'This is post')]
    #[OA\Response(response: 200, description: 'Worked well!', attachables: [new Model(type: DummyType::class)])]
    public function operationsWithOtherAnnotations()
    {
    }

    #[Route('/areas/new', methods: ['GET', 'POST'])]
    #[Areas(['area', 'area2'])]
    public function newAreaAction()
    {
    }

    #[Route('/compound', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'Worked well!', attachables: [new Model(type: CompoundEntity::class)])]
    public function compoundEntityAction()
    {
    }

    #[Route('/discriminator-mapping', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'Worked well!', attachables: [new Model(type: SymfonyDiscriminator81::class)])]
    public function discriminatorMappingAction()
    {
    }

    #[Route('/discriminator-mapping-configured-with-file', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'Worked well!', attachables: [new Model(type: SymfonyDiscriminatorFileMapping::class)])]
    public function discriminatorMappingConfiguredWithFileAction()
    {
    }

    #[Route('/named_route-operation-id', name: 'named_route_operation_id', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'success')]
    public function namedRouteOperationIdAction()
    {
    }

    #[Route('/custom-operation-id', methods: ['GET', 'POST'])]
    #[OA\Get(operationId: 'get-custom-operation-id')]
    #[OA\Post(operationId: 'post-custom-operation-id')]
    #[OA\Response(response: 200, description: 'success')]
    public function customOperationIdAction()
    {
    }

    #[Route('/swagger/symfonyConstraintsWithValidationGroups', methods: ['GET'])]
    #[OA\Response(
        response: '201',
        description: 'Used for symfony constraints with validation groups test',
        content: new Model(type: SymfonyConstraintsWithValidationGroups::class, groups: ['test']),
    )]
    public function symfonyConstraintsWithGroupsAction()
    {
    }

    #[Route('/alternate-entity-type', methods: ['GET', 'POST'])]
    #[OA\Get(operationId: 'alternate-entity-type')]
    #[OA\Response(
        response: 200,
        description: 'success',
        content: new OA\JsonContent(
            ref: new Model(type: EntityWithAlternateType81::class),
        ),
    )]
    public function alternateEntityType()
    {
    }

    #[Route('/entity-with-ref', methods: ['GET', 'POST'])]
    #[OA\Get(operationId: 'entity-with-ref')]
    #[OA\Response(
        response: 200,
        description: 'success',
        content: new OA\JsonContent(
            ref: new Model(type: EntityWithRef::class),
        ),
    )]
    public function entityWithRef()
    {
    }

    #[Route('/entity-with-object-type', methods: ['GET', 'POST'])]
    #[OA\Get(operationId: 'entity-with-object-type')]
    #[OA\Response(
        response: 200,
        description: 'success',
        content: new OA\JsonContent(
            ref: new Model(type: EntityWithObjectType::class),
        ),
    )]
    public function entityWithObjectType()
    {
    }

    #[Route('/form-with-alternate-type', methods: ['POST'])]
    #[OA\Response(
        response: 204,
        description: 'Operation automatically detected',
    )]
    #[OA\RequestBody(
        content: new Model(type: FormWithAlternateSchemaType::class),
    )]
    public function formWithAlternateSchemaType()
    {
    }

    #[Route('/form-with-ref-type', methods: ['POST'])]
    #[OA\Response(
        response: 204,
        description: 'Operation automatically detected',
    )]
    #[OA\RequestBody(
        content: new Model(type: FormWithRefType::class),
    )]
    public function formWithRefSchemaType()
    {
    }

    #[Route('/form-with-csrf-protection-enabled-type', methods: ['POST'])]
    #[OA\Response(
        response: 204,
        description: 'Operation automatically detected',
    )]
    #[OA\RequestBody(
        content: new Model(type: FormWithCsrfProtectionEnabledType::class),
    )]
    public function formWithCsrfProtectionEnabledType()
    {
    }

    #[Route('/form-with-csrf-protection-disabled-type', methods: ['POST'])]
    #[OA\Response(
        response: 204,
        description: 'Operation automatically detected',
    )]
    #[OA\RequestBody(
        content: new Model(type: FormWithCsrfProtectionDisabledType::class),
    )]
    public function formWithCsrfProtectionDisabledType()
    {
    }

    #[Route('/entity-with-nullable-property-set', methods: ['GET'])]
    #[OA\Response(
        response: 201,
        description: 'Operation automatically detected',
        attachables: [
            new Model(type: EntityWithNullableSchemaSet::class),
        ],
    )]
    public function entityWithNullableSchemaSet()
    {
    }

    #[Route('/entity-with-falsy-defaults', methods: ['GET'])]
    #[OA\Response(
        response: 204,
        description: 'Operation automatically detected',
    )]
    #[OA\RequestBody(
        content: new Model(type: EntityWithFalsyDefaults::class),
    )]
    public function entityWithFalsyDefaults()
    {
    }

    #[OA\Get(responses: [
        new OA\Response(
            response: '200',
            description: 'Success',
            attachables: [
                new Model(type: Article::class, groups: ['light']),
            ],
        ),
    ])]
    #[OA\Parameter(ref: '#/components/parameters/test')]
    #[Route('/article_attributes/{id}', methods: ['GET'])]
    #[OA\Parameter(name: 'Accept-Version', in: 'header', schema: new OA\Schema(type: 'string'))]
    public function fetchArticleActionWithAttributes()
    {
    }

    #[Areas(['area', 'area2'])]
    #[Route('/areas_attributes/new', methods: ['GET', 'POST'])]
    public function newAreaActionAttributes()
    {
    }

    #[Route('/security_attributes')]
    #[OA\Response(response: '201', description: '')]
    #[Security(name: 'api_key')]
    #[Security(name: 'basic')]
    #[Security(name: 'oauth2', scopes: ['scope_1'])]
    public function securityActionAttributes()
    {
    }

    #[Route('/security_override_attributes')]
    #[OA\Response(response: '201', description: '')]
    #[Security(name: 'api_key')]
    #[Security(name: null)]
    public function securityOverrideActionAttributes()
    {
    }

    #[Route('/inline_path_parameters')]
    #[OA\Response(response: '200', description: '')]
    public function inlinePathParameters(
        #[OA\PathParameter] string $product_id
    ) {
    }

    #[Route('/enum')]
    #[OA\Response(response: '201', description: '', attachables: [new Model(type: Article81::class)])]
    public function enum()
    {
    }

    #[Route('/range_integer', methods: ['GET'])]
    #[OA\Response(response: '200', description: '', attachables: [new Model(type: RangeInteger::class)])]
    public function rangeInteger()
    {
    }

    #[Route('/serializename', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'success', content: new Model(type: SerializedNameEntity::class))]
    public function serializedNameAction()
    {
    }

    #[Route('/name_converter_context', methods: ['GET'])]
    #[OA\Response(
        response: '200',
        description: '',
        content: new Model(type: EntityThroughNameConverter::class, serializationContext: ['secret_name_converter_value' => true])
    )]
    #[OA\Response(
        response: '201',
        description: 'Same class without context',
        content: new Model(type: EntityThroughNameConverter::class)
    )]
    public function nameConverterContext()
    {
    }

    #[Route('/arbitrary_array', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Success', content: new Model(type: Foo::class))]
    public function arbitraryArray()
    {
    }

    #[Route('/dictionary', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Success', content: new Model(type: Dictionary::class))]
    public function dictionary()
    {
    }

    #[Route('/article_map_query_string')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryString(
        #[MapQueryString] SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_string_nullable')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryStringNullable(
        #[MapQueryString] ?SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_string_passes_validation_groups')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryStringHandlesValidationGroups(
        #[MapQueryString(validationGroups: ['test'])] SymfonyConstraintsWithValidationGroups $symfonyConstraintsWithValidationGroups,
    ) {
    }

    #[Route('/article_map_query_string_overwrite_parameters')]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        schema: new OA\Schema(type: 'string', nullable: true),
        description: 'Query parameter id description'
    )]
    #[OA\Parameter(
        name: 'name',
        in: 'query',
        description: 'Query parameter name description'
    )]
    #[OA\Parameter(
        name: 'nullableName',
        in: 'query',
        description: 'Query parameter nullableName description'
    )]
    #[OA\Parameter(
        name: 'articleType81',
        in: 'query',
        description: 'Query parameter articleType81 description'
    )]
    #[OA\Parameter(
        name: 'nullableArticleType81',
        in: 'query',
        description: 'Query parameter nullableArticleType81 description'
    )]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryStringOverwriteParameters(
        #[MapQueryString] SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_string_many_parameters')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithManyParameters(
        #[MapQueryString] FilterQueryModel $filterQuery,
        #[MapQueryString] PaginationQueryModel $paginationQuery,
        #[MapQueryString] SortQueryModel $sortQuery,
        #[MapQueryString] ArrayQueryModel $arrayQuery,
    ) {
    }

    #[Route('/article_map_query_string_many_parameters_optional')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithManyOptionalParameters(
        #[MapQueryString] ?FilterQueryModel $filterQuery,
        #[MapQueryString] ?PaginationQueryModel $paginationQuery,
        #[MapQueryString] ?SortQueryModel $sortQuery,
        #[MapQueryString] ?ArrayQueryModel $arrayQuery,
    ) {
    }

    #[Route('/article_map_query_parameter')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameter(
        #[MapQueryParameter] int $someInt,
        #[MapQueryParameter] float $someFloat,
        #[MapQueryParameter] bool $someBool,
        #[MapQueryParameter] string $someString,
        #[MapQueryParameter] array $someArray,
    ) {
    }

    #[Route('/article_map_query_parameter_validate_filters')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterValidateFilters(
        #[MapQueryParameter(options: ['min_range' => 2, 'max_range' => 1234])] int $minMaxInt,
        #[MapQueryParameter(filter: FILTER_VALIDATE_DOMAIN)] string $domain,
        #[MapQueryParameter(filter: FILTER_VALIDATE_EMAIL)] string $email,
        #[MapQueryParameter(filter: FILTER_VALIDATE_IP)] string $ip,
        #[MapQueryParameter(filter: FILTER_VALIDATE_IP, flags: FILTER_FLAG_IPV4)] string $ipv4,
        #[MapQueryParameter(filter: FILTER_VALIDATE_IP, flags: FILTER_FLAG_IPV6)] string $ipv6,
        #[MapQueryParameter(filter: FILTER_VALIDATE_MAC)] string $macAddress,
        #[MapQueryParameter(filter: FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^test/'])] string $regexp,
        #[MapQueryParameter(filter: FILTER_VALIDATE_URL)] string $url,
    ) {
    }

    #[Route('/article_map_query_parameter_nullable')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterNullable(
        #[MapQueryParameter] ?int $id,
    ) {
    }

    #[Route('/article_map_query_parameter_default')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterDefault(
        #[MapQueryParameter] int $id = 123,
    ) {
    }

    #[Route('/article_map_query_parameter_overwrite_parameters')]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        description: 'Query parameter id description',
        example: 123,
    )]
    #[OA\Parameter(
        name: 'changedType',
        in: 'query',
        schema: new OA\Schema(type: 'int', nullable: false),
        description: 'Incorrectly described query parameter',
        example: 123,
    )]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterOverwriteParameters(
        #[MapQueryParameter] ?int $id,
        #[MapQueryParameter] ?string $changedType,
    ) {
    }

    #[Route('/article_map_request_payload', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createArticleFromMapRequestPayload(
        #[MapRequestPayload] Article81 $article81,
    ) {
    }

    #[Route('/article_map_request_payload_nullable', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createArticleFromMapRequestPayloadNullable(
        #[MapRequestPayload] ?Article81 $article81,
    ) {
    }

    #[Route('/article_map_request_payload_overwrite', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Request body description',
        content: new Model(type: EntityWithNullableSchemaSet::class),
    )]
    #[OA\Response(response: '200', description: '')]
    public function createArticleFromMapRequestPayloadOverwrite(
        #[MapRequestPayload] Article81 $article81,
    ) {
    }

    #[Route('/article_map_request_payload_handles_already_set_content', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Request body description',
        content: new OA\JsonContent(
            ref: new Model(type: Article81::class)
        ),
    )]
    #[OA\Response(response: '200', description: '')]
    public function createArticleFromMapRequestPayloadHandlesAlreadySetContent(
        #[MapRequestPayload] Article81 $article81,
    ) {
    }

    #[Route('/article_map_request_payload_validation_groups', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createArticleFromMapRequestPayloadPassedValidationGroups(
        #[MapRequestPayload(validationGroups: ['test'])] SymfonyConstraintsWithValidationGroups $symfonyConstraintsWithValidationGroups,
    ) {
    }
}
