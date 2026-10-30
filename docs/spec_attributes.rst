swagger-php spec attributes
================================

swagger-php 6.17 added a second set of attributes, the spec attributes in ``OpenApi\Spec``.
swagger-php 7.0 marks the classic attributes (``OpenApi\Attributes``) as deprecated.

NelmioApiDocBundle reads spec attributes on controller methods, next to the classic ones.
This allows to migrate one controller at a time.

.. note::

    Spec attributes need swagger-php 6.17 or later. With older versions they are ignored.

    Attributes on models (entities, DTOs) are only read as classic attributes for now.

Controllers
-----------

A route method uses spec attributes the same way it uses classic ones:

.. code-block:: php-attributes

    namespace App\Controller;

    use App\Entity\User;
    use Nelmio\ApiDocBundle\Attribute\Spec\Model;
    use OpenApi\Spec as OA;
    use Symfony\Component\Routing\Attribute\Route;

    #[OA\Tag(name: 'users')]
    class UserController
    {
        #[Route('/api/users/{id}', methods: ['GET'])]
        #[OA\Response(response: 200, description: 'The user', content: new Model(type: User::class, groups: ['full']))]
        #[OA\Response(response: 404, description: 'User not found')]
        public function fetchUser(
            #[OA\Parameter\Path(description: 'The user id')]
            int $id,
        ) {
            // ...
        }
    }

The route defines the operation, so most methods do not need an operation attribute.
Attributes declared on the method, or on its PHP parameters, describe the route's operation.
A parameter attribute takes its name and type from the PHP parameter.

An explicit operation, for example ``#[OA\Operation\Get(summary: '...')]``, describes the route it
sits on. Without a ``path`` it takes the route's path. An operation with a different path or HTTP
method than the route is ignored.

Attributes on the controller class apply to every operation in it. A ``#[OA\Tag]`` on the class
is added to the document's tags and to every operation of the class, inherited methods included.

Operation ids are generated as for classic attributes, unless an operation declares one.

Models
------

Inside spec attributes, use ``Nelmio\ApiDocBundle\Attribute\Spec\Model`` in place of the classic
``#[Model]``. It takes the same options: ``type``, ``groups``, ``options``,
``serializationContext`` and ``name``, see "Use Models" in :doc:`the main documentation <index>`.

.. code-block:: php-attributes

    #[OA\Response(
        response: 200,
        description: 'All users',
        content: new OA\MediaType\Json(type: 'array', items: new Model(type: User::class)),
    )]

Mixing classic and spec attributes
----------------------------------

Classic and spec attributes can be used side by side, also on the same method. Both are read and
merge into one operation. The bundle's own attributes, for example ``#[Security]``, work next to
spec attributes as before.
