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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Symfony2 command to dump Swagger-compliant JSON files.
 *
 * @author Bez Hermoso <bez@activelamp.com>
 */
class SwaggerDumpCommand extends ContainerAwareCommand
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected $destination;

    protected function configure()
    {
        $this->filesystem = new Filesystem();

        $this
            ->setDescription('Dump Swagger-compliant JSON files.')
            ->addOption('resource', '', InputOption::VALUE_OPTIONAL, 'A specific resource API declaration to dump.')
            ->addOption('list-only', '', InputOption::VALUE_NONE, 'Dump resource list only.')
            ->addArgument('destination', InputOption::VALUE_OPTIONAL, 'Directory to dump JSON files in.', null)
            ->setName('api:swagger:dump');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');

        $destination = $input->getArgument('destination');

        if (count($destination) > 0) {

            $destination = $destination[0];

            $realpath = realpath($destination);

            if ($realpath == false) {
                $rootDir = $container->getParameter('kernel.root_dir');
                $rootDir = realpath($rootDir . '/..');
                $destination = $rootDir . '/' . $destination;
            } else {
                $destination = $realpath;
            }
            $this->destination = $destination;
        } else {
            $this->destination = null;
        }

        if ($input->getOption('list-only') && $input->getOption('resource')) {
            throw new \RuntimeException('Cannot selectively dump a resource with the --list-only flag.');
        }

        $apiDocs = $extractor->all();

        if ($input->getOption('list-only')) {
            $data = $this->getResourceList($apiDocs, $output);
            $this->dump($data, null, $input, $output);
            return;
        }

        if (false != ($resource = $input->getOption('resource'))) {
            $data = $this->getApiDeclaration($apiDocs, $resource, $output);
            if (count($data['apis']) === 0) {
                throw new \InvalidArgumentException(sprintf('Resource "%s" does not exist.', $resource));
            }
            $this->dump($data, $resource, $input, $output);
            return;
        }

        $data = $this->getResourceList($apiDocs);

        if ($this->destination == null) {
            $output->writeln('');
            $output->writeln('<comment>Resource list: </comment>');
        }

        $this->dump($data, null, $input, $output, false);

        foreach ($data['apis'] as $api) {

            $resource = substr($api['path'], 1);
            if ($this->destination == null) {
                $output->writeln('');
                $output->writeln(sprintf('<comment>API declaration for <info>"%s"</info> resource: </comment>', $resource));
            }
            $data = $this->getApiDeclaration($apiDocs, $resource, $output);
            $this->dump($data, $resource, $input, $output, false);
        }
    }

    protected function dump(array $data, $resource, InputInterface $input, OutputInterface $output, $treatAsFile = true)
    {

        $content = json_encode($data, JSON_PRETTY_PRINT);

        if ($this->destination == null) {
            $output->writeln($content);
            return;
        }

        if ($resource == false) {
            if ($treatAsFile === false) {
                $path = sprintf('%s/api-docs.json', $this->destination);
            } else {
                $path = $this->destination;
            }
            $string = sprintf('<comment>Dumping resource list to %s: </comment>', $path);
            $this->writeToFile($data, $path, $output, $string);
            return;
        }

        if ($treatAsFile === false) {
            $path = sprintf('%s/%s.json', $this->destination, $resource);
        } else {
            $path = $this->destination;
        }
        $string = sprintf('<comment>Dump API declaration to %s: </comment>', $path);
        $this->writeToFile($content, $path, $output, $string);

    }

    protected function writeToFile($content, $file, OutputInterface $output, $string = null)
    {
        $message = array($string);
        try {
            $this->filesystem->dumpFile($file, $content);
            $message[] = '<info>OK</info>';
            $output->writeln(implode(' ', array_filter($message)));
        } catch (IOException $e) {
            $message[] = '<error>NOT OK</error>';
            $output->writeln(implode(' ', array_filter($message)));
        }
    }

    protected function getResourceList(array $data)
    {
        $container = $this->getContainer();
        $formatter = $container->get('nelmio_api_doc.formatter.swagger_formatter');
        $list = $formatter->format($data);
        return $list;
    }

    protected function getApiDeclaration(array $data, $resource)
    {
        $container = $this->getContainer();
        $formatter = $container->get('nelmio_api_doc.formatter.swagger_formatter');
        $list = $formatter->format($data, '/' . $resource);
        return $list;
    }
}