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
use Graze\Sprout\Seed\Seeder;
use Graze\Sprout\Seed\TableSeederFactory;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeedCommand extends Command
{
    const OPTION_CONFIG   = 'config';
    const OPTION_CHOP     = 'chop';
    const OPTION_GROUP    = 'group';
    const ARGUMENT_SCHEMA = 'schema';
    const ARGUMENT_TABLE  = 'table';

    protected function configure()
    {
        $this->setName('seed');
        $this->setDescription('Seed all the data for a given group, schema or table');

        $this->addOption(
            static::OPTION_CONFIG,
            'c',
            InputOption::VALUE_OPTIONAL,
            'The configuration file to use',
            Config::DEFAULT_CONFIG_PATH
        );

        $this->addOption(
            static::OPTION_CHOP,
            't',
            InputOption::VALUE_NONE,
            'chop (truncate) tables before seeding them'
        );

        $this->addOption(
            static::OPTION_GROUP,
            'g',
            InputOption::VALUE_OPTIONAL,
            'group to seed'
        );

        $this->addArgument(
            static::ARGUMENT_SCHEMA,
            InputArgument::OPTIONAL,
            'The schema configuration to use, if none is supplied, all schemas are run'
        );
        $this->addArgument(
            static::ARGUMENT_TABLE,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'The tables to seed, if not specified. Seed all tables that there is a file for'
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
        $schema = $input->getArgument(static::ARGUMENT_SCHEMA);
        $tables = $input->getArgument(static::ARGUMENT_TABLE);

        $config = (new Config())->parse($input->getOption(static::OPTION_CONFIG));

        $group = $input->getOption(static::OPTION_GROUP) ?: $config->get(Config::CONFIG_DEFAULT_GROUP);

        if ($input->getOption(static::OPTION_CHOP)) {
            $chopCommand = new ChopCommand();
            $exitCode = $chopCommand->run(
                new ArrayInput([
                    static::ARGUMENT_SCHEMA      => $schema,
                    static::ARGUMENT_TABLE       => $tables,
                    '--' . static::OPTION_CONFIG => $input->getOption(static::OPTION_CONFIG),
                    '--' . static::OPTION_GROUP  => $group,
                ]),
                $output
            );
            if ($exitCode !== 0) {
                throw new \RuntimeException('failed to chop down the tables');
            }
        }

        $globalPool = new Pool();

        $seedSchema = function (
            string $schema,
            array $tables = []
        ) use (
            $input,
            $output,
            $config,
            $group,
            $globalPool
        ) {
            $schemaConfiguration = $config->getSchemaConfiguration($schema);
            $schemaPath = $config->getSchemaPath($schema, $group);

            if (count($tables) === 0) {
                // find tables from existing dump
                $files = iterator_to_array(new \FilesystemIterator($schemaPath));
                $files = array_values(array_filter(
                    $files,
                    function (SplFileInfo $file) {
                        return (!in_array($file->getFilename(), ['.', '..'])
                                && pathinfo($file, PATHINFO_FILENAME) !== '');
                    }
                ));

                // sort by file size
                usort(
                    $files,
                    function (SplFileInfo $a, SplFileInfo $b) {
                        $left = $a->getSize();
                        $right = $b->getSize();
                        return ($left == $right)
                            ? 0
                            : (($left > $right) ?
                                -1 : 1);
                    }
                );
                $tables = array_map(
                    function (SplFileInfo $file) {
                        return pathinfo($file, PATHINFO_FILENAME);
                    },
                    $files
                );
            }

            $output->writeln(sprintf(
                'Seeding <info>%d</info> tables in <info>%s</info> schema for group <info>%s</info>',
                count($tables),
                $schema,
                $group
            ));

            $pool = new Pool(
                [],
                $config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES),
                false,
                ['seed', 'schema' => $schema]
            );

            $seeder = new Seeder($schemaConfiguration, $output, new TableSeederFactory($pool));
            $seeder->seed($schemaPath, $tables);

            $globalPool->add($pool);
        };

        if (is_null($schema)) {
            $files = new \FilesystemIterator($config->getGroupPath($group));
            foreach ($files as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }
                if (is_dir($file)) {
                    $seedSchema(pathinfo($file, PATHINFO_BASENAME));
                }
            }
        } else {
            $seedSchema($schema, $tables);
        }

        $processTable = new Table($output, $globalPool);
        $processTable->setShowSummary(true);

        if (!$processTable->run(0.1)) {
            return 1;
        }

        return 0;
    }
}
