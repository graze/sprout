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
use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Parser\SchemaParser;
use Graze\Sprout\Seed\Seeder;
use Graze\Sprout\Seed\TableSeederFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeedCommand extends Command
{
    const OPTION_CONFIG          = 'config';
    const OPTION_NO_CHOP         = 'no-chop';
    const OPTION_GROUP           = 'group';
    const ARGUMENT_SCHEMA_TABLES = 'schemaTables';

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
            static::OPTION_NO_CHOP,
            '',
            InputOption::VALUE_NONE,
            'do not chop (truncate) tables before seeding them'
        );

        $this->addOption(
            static::OPTION_GROUP,
            'g',
            InputOption::VALUE_OPTIONAL,
            'group to seed'
        );

        $this->addArgument(
            static::ARGUMENT_SCHEMA_TABLES,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Collection of schema and tables to use, examples: schema1 schema2 | schema1:* schema2:table1,table2'
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
        $schemas = $input->getArgument(static::ARGUMENT_SCHEMA_TABLES);
        $config = (new Config())->parse($input->getOption('config'));
        $group = $input->getOption('group') ?: $config->get(Config::CONFIG_DEFAULT_GROUP);

        if (!$input->getOption(static::OPTION_NO_CHOP)) {
            $chopCommand = new ChopCommand();
            $exitCode = $chopCommand->run(
                new ArrayInput([
                    static::ARGUMENT_SCHEMA_TABLES => $schemas,
                    '--' . static::OPTION_CONFIG   => $input->getOption(static::OPTION_CONFIG),
                    '--' . static::OPTION_GROUP    => $group,
                ]),
                $output
            );
            if ($exitCode !== 0) {
                throw new \RuntimeException('failed to chop down the tables');
            }
        }

        $schemaParser = new SchemaParser($config, $group);
        $parsedSchemas = $schemaParser->extractSchemas($schemas);

        $numTables = array_sum(array_map(
            function (ParsedSchema $schema) {
                return count($schema->getTables());
            },
            $parsedSchemas
        ));

        $useGlobal = $numTables <= 10;

        $globalPool = new Pool();
        $globalPool->setMaxSimultaneous($config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES));

        foreach ($parsedSchemas as $schema) {
            $output->writeln(sprintf(
                'Seeding <info>%d</info> tables in <info>%s</info> schema in group <info>%s</info>',
                count($schema->getTables()),
                $schema->getSchameName(),
                $group
            ));

            if ($useGlobal) {
                $pool = $globalPool;
            } else {
                $pool = new Pool(
                    [],
                    $config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES),
                    false,
                    ['seed', 'schema' => $schema->getSchameName()]
                );
                $globalPool->add($pool);
            }

            $seeder = new Seeder($schema->getSchemaConfig(), $output, new TableSeederFactory($pool));
            $seeder->seed($schema->getPath(), $schema->getTables());
        }

        $processTable = new Table($output, $globalPool);
        $processTable->setShowSummary(true);

        if (!$processTable->run(0.1)) {
            return 1;
        }

        return 0;
    }
}
