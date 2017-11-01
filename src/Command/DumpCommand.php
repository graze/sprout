<?php
/**
 * This file is part of graze/sprout.
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/sprout/blob/master/LICENSE.md
 * @link    https://github.com/graze/sprout
 */

namespace Graze\Sprout\Command;

use Exception;
use Graze\Sprout\Config;
use Graze\Sprout\Dump\Dumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    protected function configure()
    {
        $this->setName('dump');
        $this->setDescription('Dump the data for a given table to file.');

        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_OPTIONAL,
            'The configuration file to use',
            Config::DEFAULT_CONFIG_PATH
        );

        $this->addArgument('schema', InputArgument::REQUIRED, 'The schema configuration to use');
        $this->addArgument(
            'table',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'The tables to dump'
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $input->getArgument('schema');
        $tables = $input->getArgument('table');

        $config = (new Config())->parse($input->getOption('config'));

        $schemaConfiguration = $config->getSchemaConfiguration($schema);
        $schemaPath = $config->getSchemaPath($schema);

        $dumper = new Dumper($schemaConfiguration, $output);
        $dumper->dump($schemaPath, $tables);

        return 0;
    }
}
