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

use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    /**
     * @var RenderOpenApi
     */
    private $renderOpenApi;

    public function __construct(RenderOpenApi $renderOpenApi)
    {
        $this->renderOpenApi = $renderOpenApi;

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
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $area = $input->getOption('area');

        $options = [
            'no-pretty' => $input->hasParameterOption(['--no-pretty']),
        ];
        $docs = $this->renderOpenApi->render(RenderOpenApi::JSON, $area, $options);
        $output->writeln($docs, OutputInterface::OUTPUT_RAW);

        return 0;
    }
}
