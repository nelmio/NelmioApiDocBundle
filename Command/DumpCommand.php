<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $generatorLocator;

    /**
     * DumpCommand constructor.
     */
    public function __construct(ContainerInterface $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;

        parent::__construct();
    }

    /**
     * Configures the dump command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Dumps documentation in OpenAPI JSON format')
            ->addOption('area', '', InputOption::VALUE_OPTIONAL, '', 'default')
            ->addOption('no-pretty', '', InputOption::VALUE_NONE, 'Do not pretty format output')
        ;
    }

    /**
     * @throws InvalidArgumentException If the area to dump is not valid
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $area = $input->getOption('area');

        if (!$this->generatorLocator->has($area)) {
            throw new InvalidArgumentException(sprintf('Area "%s" is not supported.', $area));
        }

        $spec = $this->generatorLocator->get($area)->generate();

        if ($input->hasParameterOption(['--no-pretty'])) {
            $output->writeln(json_encode($spec));
        } else {
            $output->writeln(json_encode($spec, JSON_PRETTY_PRINT));
        }

        return 0;
    }
}
