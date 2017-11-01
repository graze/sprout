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

use Graze\ParallelProcess\Table;
use Graze\ParallelProcess\Pool;
use Graze\Sprout\Chop\Chopper;
use Graze\Sprout\Chop\TableChopperFactory;
use Graze\Sprout\Config;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ChopCommand extends Command
{
    protected function configure()
    {
        $this->setName('chop');
        $this->setAliases(['truncate']);
        $this->setDescription('Chop down (truncate) all the tables');

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
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'The tables to truncate, if not specified. Truncate will empty all the tables in a schema (not in an exclude regex)'
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $input->getArgument('schema');
        $tables = $input->getArgument('table');

        $config = (new Config())->parse($input->getOption('config'));

        $schemaConfiguration = $config->getSchemaConfiguration($schema);
        $schemaPath = $config->getSchemaPath($schema);

        if (count($tables) === 0) {
            // find tables from existing dump
            $files = new \FilesystemIterator($schemaPath);
            foreach ($files as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }
                $tables[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        $processTable = new Table($output);

        $seeder = new Chopper($schemaConfiguration, $output, new TableChopperFactory($processTable));
        $seeder->chop($tables);

        return 0;
    }
}
