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

use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\Table;
use Graze\Sprout\Chop\Chopper;
use Graze\Sprout\Chop\TableChopperFactory;
use Graze\Sprout\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

        $this->addOption(
            'group',
            'g',
            InputOption::VALUE_OPTIONAL,
            'The group to truncate'
        );

        $this->addArgument('schema', InputArgument::OPTIONAL, 'The schema configuration to use');
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

        $group = $input->getOption('group') ?: $config->get(Config::CONFIG_DEFAULT_GROUP);

        $globalPool = new Pool();
        $globalPool->setMaxSimultaneous($config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES));

        $chopSchema = function ($schema, array $tables = []) use ($output, $config, $group, $globalPool) {
            $schemaConfiguration = $config->getSchemaConfiguration($schema);
            $schemaPath = $config->getSchemaPath($schema, $group);

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

            $output->writeln(sprintf(
                'Chopping down <info>%d</info> tables in <info>%s</info> schema in group <info>%s</info>',
                count($tables),
                $schema,
                $group
            ));

            $pool = new Pool(
                [],
                $config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES),
                false,
                ['chop', 'schema' => $schema]
            );

            $chopper = new Chopper($schemaConfiguration, $output, new TableChopperFactory($pool));
            $chopper->chop($tables);

            $globalPool->add($pool);
        };

        if ($schema === null) {
            $files = new \FilesystemIterator($config->getGroupPath($group));
            foreach ($files as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }
                if (is_dir($file)) {
                    $chopSchema(pathinfo($file, PATHINFO_BASENAME));
                }
            }
        } else {
            $chopSchema($schema, $tables);
        }

        $processTable = new Table($output, $globalPool);
        $processTable->setShowSummary(true);

        if (!$processTable->run(0.1)) {
            return 1;
        }

        return 0;
    }
}
