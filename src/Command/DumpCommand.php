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
use Graze\Sprout\Config\Config;
use Graze\Sprout\Dump\Dumper;
use Graze\Sprout\Dump\TableDumperFactory;
use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Parser\SchemaParser;
use Graze\Sprout\Parser\TablePopulator;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    const ARGUMENT_SCHEMA_TABLES = 'schemaTables';

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

        $fileSystem = new Local('.');
        $tablePopulator = new TablePopulator($fileSystem);
        $schemaParser = new SchemaParser($tablePopulator, $config, $group);
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
                'Dumping <info>%d</info> tables in <info>%s</info> schema in group <info>%s</info>',
                count($schema->getTables()),
                $schema->getSchemaName(),
                $group
            ));

            if ($useGlobal) {
                $pool = $globalPool;
            } else {
                $pool = new Pool(
                    [],
                    $config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES),
                    false,
                    ['dump', 'schema' => $schema->getSchemaName()]
                );
                $globalPool->add($pool);
            }

            $dumper = new Dumper($schema->getSchemaConfig(), $output, new TableDumperFactory($pool), $fileSystem);
            $dumper->dump($schema->getPath(), $schema->getTables());
        }

        $processTable = new Table($output, $globalPool);
        $processTable->setShowSummary(true);

        if (!$processTable->run(0.1)) {
            return 1;
        }

        return 0;
    }
}
