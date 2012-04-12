<?php

namespace Nelmio\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends ContainerAwareCommand
{
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

        $extractedDoc = $this->getContainer()->get('nelmio.api.extractor.api_doc_extractor')->all();
        $result = $formatter->format($extractedDoc);

        if ('json' === $format) {
            $output->writeln(json_encode($result));
        } else {
            $output->writeln($result);
        }
    }
}
