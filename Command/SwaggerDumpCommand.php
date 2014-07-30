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
    protected function configure()
    {
        $this
            ->setDescription('Dump Swagger-compliant JSON files.')
            ->addOption('resource', '', InputOption::VALUE_OPTIONAL, 'A specific resource API declaration to dump.')
            ->addOption('all', '', InputOption::VALUE_NONE, 'Dump resource list and all API declarations.')
            ->addOption('list-only', '', InputOption::VALUE_NONE, 'Dump resource list only.')
            ->addArgument('destination', InputOption::VALUE_REQUIRED, 'Directory to dump JSON files in.', null)
            ->setName('api:swagger:dump');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $destination = $input->getArgument('destination');

        $rootDir = $container->get('kernel')->getRootDir();
        $rootDir = $rootDir . '/..';

        if (null === $destination) {
            $destination = realpath($rootDir . '/' . $destination);
        }

        $fs = new Filesystem();

        if (!$fs->exists($destination)) {
            $fs->mkdir($destination);
        }

        $destination = realpath($destination);

        if ($input->getOption('all') && $input->getOption('resource')) {
            throw new \RuntimeException('Cannot selectively dump a resource with the --all flag.');
        }

        if ($input->getOption('list-only') && $input->getOption('resource')) {
            throw new \RuntimeException('Cannot selectively dump a resource with the --list-only flag.');
        }

        if ($input->getOption('all') && $input->getOption('list-only')) {
            throw new \RuntimeException('Cannot selectively dump resource list with the --all flag.');
        }

        $output->writeln('');
        $output->writeln('Reading annotations...');
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->all();

        if ($input->getOption('list-only')) {
             $this->dumpResourceList($destination, $data, $output);
        }

        if (false != ($resource = $input->getOption('resource'))) {
            $this->dumpApiDeclaration($destination, $data, $resource, $output);
        }

        if ($input->getOption('all')) {
            $formatter = $container->get('nelmio_api_doc.formatter.swagger_formatter');
            $this->dumpResourceList($destination, $data, $output);
            $list = $formatter->format($data);
            foreach ($list['apis'] as $api) {
                $this->dumpApiDeclaration($destination, $data, substr($api['path'], 1), $output);
            }
        }
    }

    protected function dumpResourceList($destination, array $data, OutputInterface $output)
    {
        $container = $this->getContainer();
        $formatter = $container->get('nelmio_api_doc.formatter.swagger_formatter');

        $list = $formatter->format($data);

        $fs = new Filesystem();
        $path = $destination . '/api-docs.json';

        $string = sprintf('<comment>Dump resource list to %s: </comment>', $path);
        try {
            $fs->dumpFile($path, json_encode($list));
        } catch (IOException $e) {
            $output->writeln($string . ' <error>NOT OK</error>');
        }
        $output->writeln($string . '<info>OK</info>');
    }

    protected function dumpApiDeclaration($destination, array $data, $resource, OutputInterface $output)
    {
        $container = $this->getContainer();
        $formatter = $container->get('nelmio_api_doc.formatter.swagger_formatter');

        $list = $formatter->format($data, '/' . $resource);

        $fs = new Filesystem();
        $path = sprintf($destination . '/%s.json', $resource);

        $string = sprintf('<comment>Dump API declaration to %s: </comment>', $path);
        try {
            $fs->dumpFile($path, json_encode($list));
        } catch (IOException $e) {
            $output->writeln($string . ' <error>NOT OK</error>');
        }
        $output->writeln($string . '<info>OK</info>');
    }
}