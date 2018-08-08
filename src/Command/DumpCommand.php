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
use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\Table;
use Graze\Sprout\Config;
use Graze\Sprout\Dump\Dumper;
use Graze\Sprout\Dump\TableDumperFactory;
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

        $this->addOption(
            'group',
            'g',
            InputOption::VALUE_OPTIONAL,
            'The group to use'
        );

        $this->addArgument('schema', InputArgument::REQUIRED, 'The schema configuration to use');
        $this->addArgument(
            'table',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
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

        $group = $input->getOption('group') ?: $config->get(Config::CONFIG_DEFAULT_GROUP);

        $schemaConfiguration = $config->getSchemaConfiguration($schema);
        $schemaPath = $config->getSchemaPath($schema);

        if (count($tables) === 0) {
            // find tables from existing dump
            $files = new \FilesystemIterator($schemaPath);
            foreach ($files as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }
                $file = pathinfo($file, PATHINFO_FILENAME);
                if (empty($file)) {
                    continue;
                }
                $tables[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        $pool = new Pool();
        $pool->setMaxSimultaneous($config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES));

        $output->writeln(sprintf(
            'Dumping <info>%d</info> tables in <info>%s</info> schema in group <info>%s</info>',
            count($tables),
            $schema,
            $group
        ));

        $dumper = new Dumper($schemaConfiguration, $output, new TableDumperFactory($pool));
        $dumper->dump($schemaPath, $tables);

        $processTable = new Table($output, $pool);
        $processTable->setShowSummary(true);

        if (!$processTable->run(0.1)) {
            return 1;
        }

        return 0;
    }
}
