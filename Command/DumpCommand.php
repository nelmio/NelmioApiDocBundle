<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Command;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

class DumpCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    protected $availableFormats = array('markdown', 'json', 'html');

    protected function configure()
    {
        $this
            ->setDescription('')
            ->addOption(
                'format', '', InputOption::VALUE_REQUIRED,
                'Output format like: ' . implode(', ', $this->availableFormats),
                $this->availableFormats[0]
            )
            ->addOption('view', '', InputOption::VALUE_OPTIONAL, '', ApiDoc::DEFAULT_VIEW)
            ->addOption('no-sandbox', '', InputOption::VALUE_NONE)
            ->setName('api:doc:dump')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getOption('format');
        $view = $input->getOption('view');

        $routeCollection = $this->getContainer()->get('router')->getRouteCollection();

        if ($format == 'json') {
            $formatter = $this->getContainer()->get('nelmio_api_doc.formatter.simple_formatter');
        } else {
            if (!in_array($format, $this->availableFormats)) {
                throw new \RuntimeException(sprintf('Format "%s" not supported.', $format));
            }

            $formatter = $this->getContainer()->get(sprintf('nelmio_api_doc.formatter.%s_formatter', $format));
        }

        if ($input->getOption('no-sandbox') && 'html' === $format) {
            $formatter->setEnableSandbox(false);
        }

        if ('html' === $format && method_exists($this->getContainer(), 'enterScope')) {
            $this->getContainer()->enterScope('request');
            $this->getContainer()->set('request', new Request(), 'request');
        }

        $extractedDoc = $this->getContainer()->get('nelmio_api_doc.extractor.api_doc_extractor')->all($view);
        $formattedDoc = $formatter->format($extractedDoc);

        if ('json' === $format) {
            $output->writeln(json_encode($formattedDoc));
        } else {
            $output->writeln($formattedDoc, OutputInterface::OUTPUT_RAW);
        }
    }
}
