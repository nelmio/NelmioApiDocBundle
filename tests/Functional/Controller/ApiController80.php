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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\ArticleInterface;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\CompoundEntity;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityThroughNameConverter;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithAlternateType80;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithFalsyDefaults;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithNullableSchemaSet;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithObjectType;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithRef;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\RangeInteger;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraints80;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraintsWithValidationGroups;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyDiscriminator80;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyDiscriminatorFileMapping;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;
use Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\SerializedNameEnt;
use Nelmio\ApiDocBundle\Tests\Functional\Form\DummyType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithAlternateSchemaType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithCsrfProtectionDisabledType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithCsrfProtectionEnabledType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithModel;
use Nelmio\ApiDocBundle\Tests\Functional\Form\FormWithRefType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\UserType;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

class ApiController80
{
    /**
     * @OA\Get(
     *
     *  @OA\Response(
     *   response="200",
     *   description="Success",
     *
     *   @Model(type=Article::class, groups={"light"}))
     *  )
     * )
     *
     * @OA\Parameter(ref="#/components/parameters/test")
     *
     * @Route("/article/{id}", methods={"GET"})
     *
     * @OA\Parameter(name="Accept-Version", in="header", @OA\Schema(type="string"))
     * @OA\Parameter(name="Application-Name", in="header", @OA\Schema(type="string"))
     */
    public function fetchArticleAction()
    {
    }

    /**
     * @OA\Get(
     *
     *  @OA\Response(
     *   response="200",
     *   description="Success",
     *
     *   @Model(type=ArticleInterface::class, groups={"light"}))
     *  )
     * )
     *
     * @OA\Parameter(ref="#/components/parameters/test")
     *
     * @Route("/article-interface/{id}", methods={"GET"})
     *
     * @OA\Parameter(name="Accept-Version", in="header", @OA\Schema(type="string"))
     * @OA\Parameter(name="Application-Name", in="header", @OA\Schema(type="string"))
     */
    public function fetchArticleInterfaceAction()
    {
    }

    /**
     * The method LINK is not supported by OpenAPI so the method will be ignored.
     *
     * @Route("/swagger", methods={"GET", "LINK"})
     * @Route("/swagger2", methods={"GET"})
     *
     * @Operation(
     *
     *     @OA\Response(response="201", description="An example resource")
     * )
     *
     * @OA\Get(
     *     path="/api/swagger2",
     *
     *     @OA\Parameter(name="Accept-Version", in="header", @OA\Schema(type="string"))
     * )
     *
     * @OA\Post(
     *     path="/api/swagger2",
     *
     *     @OA\Response(response="203", description="but 203 is not actually allowed (wrong method)")
     * )
     */
    public function swaggerAction()
    {
    }

    /**
     * @Route("/swagger/implicit", methods={"GET", "POST"})
     *
     * @OA\Response(
     *    response="201",
     *    description="Operation automatically detected",
     *
     *    @Model(type=User::class)
     * ),
     *
     * @OA\RequestBody(
     *    description="This is a request body",
     *
     *    @OA\JsonContent(
     *      type="array",
     *
     *      @OA\Items(ref=@Model(type=User::class))
     *    )
     * )
     *
     * @OA\Tag(name="implicit")
     */
    public function implicitSwaggerAction()
    {
    }

    /**
     * @Route("/test/users/{user}", methods={"POST"}, schemes={"https"}, requirements={"user"="/foo/"})
     *
     * @OA\Response(
     *    response="201",
     *    description="Operation automatically detected",
     *
     *    @Model(type=User::class)
     * ),
     *
     * @OA\RequestBody(
     *    description="This is a request body",
     *
     *    @Model(type=UserType::class, options={"bar": "baz"}))
     * )
     */
    public function submitUserTypeAction()
    {
    }

    /**
     * @Route("/test/{user}", methods={"GET"}, schemes={"https"}, requirements={"user"="/foo/"})
     *
     * @OA\Response(response=200, description="sucessful")
     */
    public function userAction()
    {
    }

    /**
     * This action is deprecated.
     *
     * Please do not use this action.
     *
     * @Route("/deprecated", methods={"GET"})
     *
     * @deprecated
     */
    public function deprecatedAction()
    {
    }

    /**
     * This action is not documented. It is excluded by the config.
     *
     * @Route("/admin", methods={"GET"})
     */
    public function adminAction()
    {
    }

    /**
     * @OA\Get(
     *     path="/filtered",
     *
     *     @OA\Response(response="201", description="")
     * )
     */
    public function filteredAction()
    {
    }

    /**
     * @Route("/form", methods={"POST"})
     *
     * @OA\RequestBody(
     *    description="Request content",
     *
     *    @Model(type=DummyType::class))
     * )
     *
     * @OA\Response(response="201", description="")
     */
    public function formAction()
    {
    }

    /**
     * @Route("/form-model", methods={"POST"})
     *
     * @OA\RequestBody(
     *    description="Request content",
     *
     *    @Model(type=FormWithModel::class))
     * )
     *
     * @OA\Response(response="201", description="")
     */
    public function formWithModelAction()
    {
    }

    /**
     * @Route("/security")
     *
     * @OA\Response(response="201", description="")
     *
     * @Security(name="api_key")
     * @Security(name="basic")
     * @Security(name="oauth2", scopes={"scope_1"})
     */
    public function securityAction()
    {
    }

    /**
     * @Route("/securityOverride")
     *
     * @OA\Response(response="201", description="")
     *
     * @Security(name="api_key")
     * @Security(name=null)
     */
    public function securityActionOverride()
    {
    }

    /**
     * @Route("/swagger/symfonyConstraints", methods={"GET"})
     *
     * @OA\Response(
     *    response="201",
     *    description="Used for symfony constraints test",
     *
     *    @Model(type=SymfonyConstraints80::class)
     * )
     */
    public function symfonyConstraintsAction()
    {
    }

    /**
     * @OA\Response(
     *     response="200",
     *     description="Success",
     *     ref="#/components/schemas/Test"
     *  ),
     * @OA\Response(
     *     response="201",
     *     ref="#/components/responses/201"
     *  )
     *
     * @Route("/configReference", methods={"GET"})
     */
    public function configReferenceAction()
    {
    }

    /**
     * @Route("/multi-annotations", methods={"GET", "POST"})
     *
     * @OA\Get(description="This is the get operation")
     *
     * @OA\Post(description="This is post")
     *
     * @OA\Response(response=200, description="Worked well!", @Model(type=DummyType::class))
     */
    public function operationsWithOtherAnnotations()
    {
    }

    /**
     * @Route("/areas/new", methods={"GET", "POST"})
     *
     * @Areas({"area", "area2"})
     */
    public function newAreaAction()
    {
    }

    /**
     * @Route("/compound", methods={"GET", "POST"})
     *
     * @OA\Response(response=200, description="Worked well!", @Model(type=CompoundEntity::class))
     */
    public function compoundEntityAction()
    {
    }

    /**
     * @Route("/discriminator-mapping", methods={"GET", "POST"})
     *
     * @OA\Response(response=200, description="Worked well!", @Model(type=SymfonyDiscriminator80::class))
     */
    public function discriminatorMappingAction()
    {
    }

    /**
     * @Route("/discriminator-mapping-configured-with-file", methods={"GET", "POST"})
     *
     * @OA\Response(response=200, description="Worked well!", @Model(type=SymfonyDiscriminatorFileMapping::class))
     */
    public function discriminatorMappingConfiguredWithFileAction()
    {
    }

    /**
     * @Route("/named_route-operation-id", name="named_route_operation_id", methods={"GET", "POST"})
     *
     * @OA\Response(response=200, description="success")
     */
    public function namedRouteOperationIdAction()
    {
    }

    /**
     * @Route("/custom-operation-id", methods={"GET", "POST"})
     *
     * @OA\Get(operationId="get-custom-operation-id")
     *
     * @OA\Post(operationId="post-custom-operation-id")
     *
     * @OA\Response(response=200, description="success")
     */
    public function customOperationIdAction()
    {
    }

    /**
     * @Route("/swagger/symfonyConstraintsWithValidationGroups", methods={"GET"})
     *
     * @OA\Response(
     *    response="201",
     *    description="Used for symfony constraints with validation groups test",
     *
     *    @Model(type=SymfonyConstraintsWithValidationGroups::class, groups={"test"})
     * )
     */
    public function symfonyConstraintsWithGroupsAction()
    {
    }

    /**
     * @Route("/alternate-entity-type", methods={"GET", "POST"})
     *
     * @OA\Get(operationId="alternate-entity-type")
     *
     * @OA\Response(response=200, description="success", @OA\JsonContent(
     *      ref=@Model(type=EntityWithAlternateType80::class),
     * ))
     */
    public function alternateEntityType()
    {
    }

    /**
     * @Route("/entity-with-ref", methods={"GET", "POST"})
     *
     * @OA\Get(operationId="entity-with-ref")
     *
     * @OA\Response(response=200, description="success", @OA\JsonContent(
     *      ref=@Model(type=EntityWithRef::class),
     * ))
     */
    public function entityWithRef()
    {
    }

    /**
     * @Route("/entity-with-object-type", methods={"GET", "POST"})
     *
     * @OA\Get(operationId="entity-with-object-type")
     *
     * @OA\Response(response=200, description="success", @OA\JsonContent(
     *      ref=@Model(type=EntityWithObjectType::class),
     * ))
     */
    public function entityWithObjectType()
    {
    }

    /**
     * @Route("/form-with-alternate-type", methods={"POST"})
     *
     * @OA\Response(
     *    response="204",
     *    description="Operation automatically detected",
     * ),
     *
     * @OA\RequestBody(
     *
     *    @Model(type=FormWithAlternateSchemaType::class))
     * )
     */
    public function formWithAlternateSchemaType()
    {
    }

    /**
     * @Route("/form-with-ref-type", methods={"POST"})
     *
     * @OA\Response(
     *    response="204",
     *    description="Operation automatically detected",
     * ),
     *
     * @OA\RequestBody(
     *
     *    @Model(type=FormWithRefType::class))
     * )
     */
    public function formWithRefSchemaType()
    {
    }

    /**
     * @Route("/form-with-csrf-protection-enabled-type", methods={"POST"})
     *
     * @OA\Response(
     *    response="204",
     *    description="Operation automatically detected",
     * ),
     *
     * @OA\RequestBody(
     *
     *    @Model(type=FormWithCsrfProtectionEnabledType::class)
     * )
     */
    public function formWithCsrfProtectionEnabledType()
    {
    }

    /**
     * @Route("/form-with-csrf-protection-disabled-type", methods={"POST"})
     *
     * @OA\Response(
     *    response="204",
     *    description="Operation automatically detected",
     * ),
     *
     * @OA\RequestBody(
     *
     *    @Model(type=FormWithCsrfProtectionDisabledType::class)
     * )
     */
    public function formWithCsrfProtectionDisabledType()
    {
    }

    /**
     * @Route("/entity-with-nullable-property-set", methods={"GET"})
     *
     * @OA\Response(
     *    response="201",
     *    description="Operation automatically detected",
     *
     *    @Model(type=EntityWithNullableSchemaSet::class)
     * )
     */
    public function entityWithNullableSchemaSet()
    {
    }

    /**
     * @Route("/entity-with-falsy-defaults", methods={"POST"})
     *
     * @OA\Response(
     *     response="204",
     *     description="Operation automatically detected",
     *  ),
     *
     * @OA\RequestBody(
     *
     *     @Model(type=EntityWithFalsyDefaults::class))
     *  )*/
    public function entityWithFalsyDefaults()
    {
    }

    /**
     * @Route("/range_integer", methods={"GET"})
     *
     * @OA\Response(
     *      response="200",
     *      description="",
     *
     *      @Model(type=RangeInteger::class)
     *  )
     */
    public function rangeInteger()
    {
    }

    /**
     * @OA\Response(
     *     response="200",
     *     description="success",
     *
     *     @Model(type=SerializedNameEnt::class)
     * )
     *
     * @Route("/serializename", methods={"GET"})
     */
    public function serializedNameAction()
    {
    }

    /**
     * @Route("/name_converter_context", methods={"GET"})
     *
     * @OA\Response(
     *    response="200",
     *    description="",
     *
     *    @Model(type=EntityThroughNameConverter::class, serializationContext={"secret_name_converter_value"=true})
     * )
     *
     * @OA\Response(
     *     response="201",
     *     description="Same class without context",
     *
     *     @Model(type=EntityThroughNameConverter::class)
     *  )
     */
    public function nameConverterContext()
    {
    }

    /**
     * @Route("/arbitrary_array", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=Foo::class)
     * )
     */
    public function arbitraryArray()
    {
    }

    /**
     * @Route("/dictionary", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=Dictionary::class)
     * )
     */
    public function dictionary()
    {
    }
}
