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

use Nelmio\ApiDocBundle\ApiDocGenerator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DumpCommand extends Command
{
    protected static $defaultName = 'app:doc:dump';

    private $generatorLocator;

    /**
     * @param ContainerInterface $generatorLocator
     */
    public function __construct($generatorLocator)
    {
        if (!$generatorLocator instanceof ContainerInterface) {
            if (!$generatorLocator instanceof ApiDocGenerator) {
                throw new \InvalidArgumentException(sprintf('Providing an instance of "%s" to "%s" is not supported.', get_class($generatorLocator), __METHOD__));
            }

            @trigger_error(sprintf('Providing an instance of "%s" to "%s()" is deprecated since version 3.1. Provide it an instance of "%s" instead.', ApiDocGenerator::class, __METHOD__, ContainerInterface::class), E_USER_DEPRECATED);
            $generatorLocator = new ServiceLocator(['default' => function () use ($generatorLocator): ApiDocGenerator {
                return $generatorLocator;
            }]);
        }

        $this->generatorLocator = $generatorLocator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Dumps API documentation in Swagger JSON format')
            ->addOption('area', '', InputOption::VALUE_OPTIONAL, '', 'default')
            ->addOption('--no-pretty', '', InputOption::VALUE_NONE, 'Do not pretty format output')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $area = $input->getOption('area');

        if (!$this->generatorLocator->has($area)) {
            throw new BadRequestHttpException(sprintf('Area "%s" is not supported.', $area));
        }

        $spec = $this->generatorLocator->get($area)->generate()->toArray();

        if( $input->hasParameterOption(['--no-pretty'])) {
            $output->writeln(json_encode($spec));
        } else {
            $output->writeln(json_encode($spec, JSON_PRETTY_PRINT));
        }
    }
}
