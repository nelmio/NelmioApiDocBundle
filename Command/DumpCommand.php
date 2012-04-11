<?php

namespace Nelmio\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected $annotationClass  = 'Nelmio\\ApiBundle\\Annotation\\ApiDoc';

    /**
     * @var array
     */
    protected $availableFormats = array('markdown', 'json');

    protected function configure()
    {
        $this
            ->setDescription('')
            ->addOption(
                'format', '', InputOption::VALUE_REQUIRED,
                'Output format like: ' . implode(', ', $this->availableFormats),
                $this->availableFormats[0]
            )
            ->setName('api:doc:dump')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getOption('format');
        $routeCollection = $this->getContainer()->get('router')->getRouteCollection();

        if (!$input->hasOption('format') || in_array($format, array('json'))) {
            $formatter = $this->getContainer()->get('nelmio.api.formatter.simple_formatter');
        } else {
            $formatter = $this->getContainer()->get(sprintf('nelmio.api.formatter.%s_formatter', $format));
        }

        $results = array();
        foreach ($routeCollection->all() as $route) {
            preg_match('#(.+)::([\w]+)#', $route->getDefault('_controller'), $matches);
            $method = new \ReflectionMethod($matches[1], $matches[2]);

            if ($annot = $this->getContainer()->get('annotation_reader')->getMethodAnnotation($method, $this->annotationClass)) {
                $results[] = $formatter->format($annot, $route);
            }
        }

        if ('json' === $format) {
            $output->writeln(json_encode($results));
        } else {
            foreach ($results as $result) {
                $output->writeln($result);
            }
        }
    }
}
