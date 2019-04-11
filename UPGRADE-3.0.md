Upgrading From 2.x To 3.0
=========================

In 3.0 we did major changes. The biggest is the removal of the `@ApiDoc`
annotation. To help you migrate to 3.0, we created this guide.

Thanks to a command created by @dbu, it should not take too long.

Step 1: Migrate to Swagger-PHP commands
---------------------------------------

First, copy this command in ``src/AppBundle/Command/SwaggerDocblockConvertCommand.php``:

```php
<?php

// src/AppBundle/Command/SwaggerDocblockConvertCommand.php
namespace AppBundle\Command;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Converts ApiDoc annotations to Swagger-PHP annotations.
 *
 * @author David Buchmann <david@liip.ch>
 * @author Guilhem Niot <guilhem.niot@gmail.com>
 */
class SwaggerDocblockConvertCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('')
            ->setName('api:doc:convert')
            ->addOption('views', null, InputOption::VALUE_OPTIONAL, 'Comma separated list of views to convert the documentation for', 'default')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $views = explode(',', $input->getOption('views'));

        if (!$this->getContainer()->has('nelmio_api_doc.extractor.api_doc_extractor')) {
            if (!$this->getContainer()->has('nelmio_api_doc.controller.swagger_ui')) {
                throw new \RuntimeException('NelmioApiDocBundle is not installed. Please run `composer require nelmio/api-doc-bundle`.');
            } else {
                throw new \RuntimeException('This command only works with NelmioApiDocBundle 2.x installed while version 3.x is currently installed. Please downgrade to 2.x to execute this command and bump your constraint only after its execution.');                      
            }
        }

        $extractor = $this->getContainer()->get('nelmio_api_doc.extractor.api_doc_extractor');

        $apiDocs = [];
        foreach ($views as $view) {
            $apiDocs = array_merge($apiDocs, $extractor->extractAnnotations($extractor->getRoutes(), $view));
        }

        foreach ($apiDocs as $annotation) {
            /** @var ApiDoc $apiDoc */
            $apiDoc = $annotation['annotation'];

            $refl = $extractor->getReflectionMethod($apiDoc->getRoute()->getDefault('_controller'));

            $this->rewriteClass($refl->getFileName(), $refl, $apiDoc);
        }
    }

    /**
     * Rewrite class with correct apidoc.
     */
    private function rewriteClass(string $path, \ReflectionMethod $method, ApiDoc $apiDoc)
    {
        echo "Processing $path::{$method->name}\n";
        $code = file_get_contents($path);
        $old = $this->locateNelmioAnnotation($code, $method->name);

        $code = substr_replace($code, $this->renderSwaggerAnnotation($apiDoc, $method), $old['start'], $old['length']);
        $code = str_replace('use Nelmio\ApiDocBundle\Annotation\ApiDoc;', "use Nelmio\ApiDocBundle\Annotation\Operation;\nuse Nelmio\ApiDocBundle\Annotation\Model;\nuse Swagger\Annotations as SWG;", $code);

        file_put_contents($path, $code);
    }

    private function renderSwaggerAnnotation(ApiDoc $apiDoc, \ReflectionMethod $method): string
    {
        $info = $apiDoc->toArray();
        if ($apiDoc->getResource()) {
            throw new \RuntimeException('implement me');
        }
        $path = str_replace('.{_format}', '', $apiDoc->getRoute()->getPath());

        $annotation = '@Operation(
     *     tags={"'.$apiDoc->getSection().'"},
     *     summary="'.$this->escapeQuotes($apiDoc->getDescription()).'"';

        foreach ($apiDoc->getFilters() as $name => $parameter) {
            $description = array_key_exists('description', $parameter) && null !== $parameter['description']
                ? $this->escapeQuotes($parameter['description'])
                : 'todo';

            $annotation .= ',
     *     @SWG\Parameter(
     *         name="'.$name.'",
     *         in="query",
     *         description="'.$description.'",
     *         required='.(array_key_exists($name, $apiDoc->getRequirements()) ? 'true' : 'false').',
     *         type="'.$this->determineDataType($parameter).'"
     *     )';
        }

        // Put parameters for POST requests into formData, as Swagger cannot handle more than one body parameter
        $in = 'POST' === $apiDoc->getMethod()
            ? 'formData'
            : 'body';

        foreach ($apiDoc->getParameters() as $name => $parameter) {
            $description = array_key_exists('description', $parameter)
                ? $this->escapeQuotes($parameter['description'])
                : 'todo';

            $annotation .= ',
     *     @SWG\Parameter(
     *         name="'.$name.'",
     *         in="'.$in.'",
     *         description="'.$description.'",
     *         required='.(array_key_exists($name, $apiDoc->getRequirements()) ? 'true' : 'false');

            if ('POST' !== $apiDoc->getMethod()) {
                $annotation .= ',
     *         @SWG\Schema(type="'.$this->determineDataType($parameter).'")';
            } else {
                $annotation .= ',
     *         type="'.$this->determineDataType($parameter).'"';
            }

            $annotation .= '
     *     )';
        }

        if (array_key_exists('statusCodes', $info)) {
            $responses = $info['statusCodes'];
            foreach ($responses as $code => $description) {
                $responses[$code] = reset($description);
            }
        } else {
            $responses = [200 => 'Returned when successful'];
        }

        $responseMap = $apiDoc->getResponseMap();
        foreach ($responses as $code => $description) {
            $annotation .= ",
     *     @SWG\\Response(
     *         response=\"$code\",
     *         description=\"{$this->escapeQuotes($description)}\"";
            if (200 === $code && isset($responseMap[$code]['class'])) {
                $model = $responseMap[$code]['class'];
                $annotation .= ",
     *         @SWG\\Schema(ref=@Model(type=\"$model\"))";
            }
            $annotation .= '
     *     )';
        }

        $annotation .= '
     * )
     *';

        return $annotation;
    }

    /**
     * @return array with `start` position and `length`
     */
    private function locateNelmioAnnotation(string $code, string $methodName): array
    {
        $position = strpos($code, "tion $methodName(");
        if (false === $position) {
            throw new \RuntimeException("Method $methodName not found in controller.");
        }

        $docstart = strrpos(substr($code, 0, $position), '@ApiDoc');
        if (false === $docstart) {
            //If action is defined more than once. Should continue and don't throw exception
            $docstart = strrpos(substr($code, 0, $position), '@Operation');
            if (false === $docstart) {

                throw new \RuntimeException("Method $methodName has no @ApiDoc annotation around\n".substr($code, $position - 200, 150));
            }
        }
        $docend = strpos($code, '* )', $docstart) + 3;

        return [
            'start' => $docstart,
            'length' => $docend - $docstart,
        ];
    }

    private function escapeQuotes(string $str = null): string
    {
        if ($str === null) {
            return '';
        }
        $lines = [];
        foreach (explode("\n", $str) as $line) {
            $lines[] = trim($line, ' *');
        }

        return str_replace('"', '""', implode(' ', $lines));
    }

    private function determineDataType(array $parameter): string
    {
        $dataType = isset($parameter['dataType']) ? $parameter['dataType'] : 'string';
        $transform = [
            'float' => 'number',
            'datetime' => 'string',
        ];
        if (array_key_exists($dataType, $transform)) {
            $dataType = $transform[$dataType];
        }

        return $dataType;
    }
}
```

Then open a command console, enter your project directory and run:

```
bin/console api:doc:convert
```

Your annotations should all be converted.

Note that this tool is here to help you but not all features are supported so
make sure the generated annotations still fit your needs.

Step 2: Update your routing
---------------------------

With NelmioApiDocBundle 2.x, you had to load the
``@NelmioApiDocBundle/Resources/config/routing.yml`` file. In 3.0, it was renamed
to ``@NelmioApiDocBundle/Resources/config/routing/swaggerui.xml``, so you have
to update the ``NelmioApiDocBundle`` route to:

```yml
# app/config/routing.yml
NelmioApiDocBundle:
    resource: "@NelmioApiDocBundle/Resources/config/routing/swaggerui.xml"
    prefix:   /api/doc
```

Step 3: Update your config
--------------------------

* ``nelmio_api_doc.name`` was replaced by ``nelmio_api_doc.documentation.info.title``.

  Before:

  ```yml
  nelmio_api_doc:
      name: My Awesome App!
  ```

  After:

  ```yml
  nelmio_api_doc:
      documentation:
          info:
              title: My Awesome App!
  ```

* ``nelmio_api_doc.swagger.api_version`` was replaced by ``nelmio_api_doc.documentation.info.version``.

* ``nelmio_api_doc.swagger.info.title`` was replaced by ``nelmio_api_doc.documentation.info.title``.

* ``nelmio_api_doc.swagger.info.description`` was replaced by ``nelmio_api_doc.documentation.info.description``.

* Other options were removed.

Step 4: Update the bundle
-------------------------

Change the constraint of ``nelmio/api-doc-bundle`` in your ``composer.json`` file
to ``^3.0``:

```json
{
    "require": {
        "nelmio/api-doc-bundle": "^3.0"
    }
}
```

Then update your dependencies:

```
composer update
```

Step 5: Review the changes
--------------------------

It's almost finished!

As most of the changes were automated you should check that they did not break
anything. Run your test suite, review, do whatever you think is useful before
pushing the changes.

Then, commit the changes, push them, and enjoy!
