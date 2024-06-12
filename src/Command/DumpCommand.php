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

use Nelmio\ApiDocBundle\Render\Html\AssetsMode;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @final
 */
class DumpCommand extends Command
{
    private RenderOpenApi $renderOpenApi;

    /**
     * @var mixed[]
     */
    private $defaultHtmlConfig = [
        'assets_mode' => AssetsMode::CDN,
        'swagger_ui_config' => [],
        'redocly_config' => [],
    ];

    public function __construct(RenderOpenApi $renderOpenApi)
    {
        $this->renderOpenApi = $renderOpenApi;

        parent::__construct();
    }

    /**
     * Configures the dump command.
     */
    protected function configure(): void
    {
        $availableFormats = $this->renderOpenApi->getAvailableFormats();
        $this
            ->setDescription('Dumps documentation in OpenAPI format to: '.implode(', ', $availableFormats))
            ->addOption('area', '', InputOption::VALUE_OPTIONAL, '', 'default')
            ->addOption(
                'format',
                '',
                InputOption::VALUE_REQUIRED,
                'Output format like: '.implode(', ', $availableFormats),
                RenderOpenApi::JSON
            )
            ->addOption('server-url', '', InputOption::VALUE_REQUIRED, 'URL where live api doc is served')
            ->addOption('html-config', '', InputOption::VALUE_REQUIRED, '', json_encode($this->defaultHtmlConfig))
            ->addOption('no-pretty', '', InputOption::VALUE_NONE, 'Do not pretty format JSON output')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $area = $input->getOption('area');
        $format = $input->getOption('format');

        $options = [];
        if (RenderOpenApi::HTML === $format) {
            $rawHtmlConfig = json_decode($input->getOption('html-config'), true);
            $options = is_array($rawHtmlConfig) ? $rawHtmlConfig + $this->defaultHtmlConfig : $this->defaultHtmlConfig;
        } elseif (RenderOpenApi::JSON === $format) {
            $options = [
                'no-pretty' => $input->hasParameterOption(['--no-pretty']),
            ];
        }

        if (null !== $input->getOption('server-url')) {
            $options['server_url'] = $input->getOption('server-url');
        }

        $docs = $this->renderOpenApi->render($format, $area, $options);
        $output->writeln($docs, OutputInterface::OUTPUT_RAW);

        return 0;
    }
}
