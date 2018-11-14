<?php
/**
 * This file is part of graze/sprout.
 *
 * Copyright © 2018 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/sprout/blob/master/LICENSE.md
 * @link    https://github.com/graze/sprout
 */

namespace Graze\Sprout\Command;

use Graze\ParallelProcess\Display\Table;
use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\PriorityPool;
use Graze\Sprout\Config\Config;
use Graze\Sprout\Db\Parser\SchemaParser;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Dump\Dumper;
use Graze\Sprout\Dump\TableDumperFactory;
use Graze\Sprout\File\Format;
use Graze\Sprout\File\Populator\FileTablePopulator;
use Graze\Sprout\File\Populator\SeedFilePopulator;
use Graze\Sprout\SeedDataInterface;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    const OPTION_CONFIG    = 'config';
    const OPTION_GROUP     = 'group';
    const OPTION_FORMAT    = 'format';
    const OPTION_SEED_TYPE = 'seedType';
    const OPTION_OVERWRITE = 'overwrite';

    const ARGUMENT_SCHEMA_TABLES = 'schemaTables';

    protected function configure()
    {
        $this->setName('dump');
        $this->setDescription('Dump the data for a given table to file.');

        $this->addOption(
            static::OPTION_CONFIG,
            'c',
            InputOption::VALUE_OPTIONAL,
            'The configuration file to use',
            Config::DEFAULT_CONFIG_PATH
        );

        $this->addOption(
            static::OPTION_GROUP,
            'g',
            InputOption::VALUE_OPTIONAL,
            'The group to use'
        );

        $this->addOption(
            static::OPTION_FORMAT,
            'f',
            InputOption::VALUE_OPTIONAL,
            'The format to use, one of: sql, csv, yaml, json. (Default: sql)',
            Format::TYPE_SQL
        );

        $this->addOption(
            static::OPTION_SEED_TYPE,
            '',
            InputOption::VALUE_OPTIONAL,
            "The seed type to apply, possible values: "
            . "<comment>truncate</comment>, <comment>ignore</comment>, <comment>update</comment>. "
            . "(Default: <comment>truncate</comment>). "
            . "This is only applicable for the formats: <comment>yaml</comment> and <comment>json</comment>",
            SeedDataInterface::SEED_TYPE_TRUNCATE
        );

        $this->addOption(
            static::OPTION_OVERWRITE,
            '',
            InputOption::VALUE_OPTIONAL,
            sprintf(
                "Should this overwrite any existing seeds for a table with the supplied "
                . "--<info>%s</info> and --<info>%s</info> options. Otherwise any existing values will be used",
                static::OPTION_FORMAT,
                static::OPTION_SEED_TYPE
            )
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
     * @throws \Graze\ConfigValidation\Exceptions\ConfigValidationFailedException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemas = $input->getArgument(static::ARGUMENT_SCHEMA_TABLES);
        $config = (new Config())->parse($input->getOption(static::OPTION_CONFIG));
        $group = $input->getOption(static::OPTION_GROUP) ?: $config->get(Config::CONFIG_DEFAULT_GROUP);
        $format = Format::parseFormat($input->getOption(static::OPTION_FORMAT));
        $seedType = strtolower($input->getOption(static::OPTION_SEED_TYPE));
        $override = (bool) $input->getOption(static::OPTION_OVERWRITE);

        $expectedSeedTypes = [SeedDataInterface::SEED_TYPE_TRUNCATE, SeedDataInterface::SEED_TYPE_IGNORE, SeedDataInterface::SEED_TYPE_UPDATE];
        if (!in_array($seedType, $expectedSeedTypes)) {
            throw new \InvalidArgumentException(
                "Option: --seed_type: {$seedType} must be one of: "
                . implode(', ', $expectedSeedTypes)
            );
        }

        $filesystem = new Local('/');
        $schemaParser = new SchemaParser(
            $config,
            $group,
            new FileTablePopulator($filesystem),
            new SeedFilePopulator($filesystem)
        );
        $parsedSchemas = $schemaParser->extractSchemas($schemas);

        $numTables = array_sum(array_map(
            function (Schema $schema) {
                return count($schema->getTables());
            },
            $parsedSchemas
        ));

        $useGlobal = $numTables <= 10;

        $globalPool = new PriorityPool();
        $globalPool->setMaxSimultaneous($config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES));

        foreach ($parsedSchemas as $schema) {
            $output->writeln(sprintf(
                'Dumping <info>%d</info> tables in <info>%s</info> schema in group <info>%s</info> to <info>%s</info>',
                count($schema->getTables()),
                $schema->getSchemaName(),
                $group,
                $schema->getPath()
            ));

            if ($useGlobal) {
                $pool = $globalPool;
            } else {
                $pool = new Pool(
                    [],
                    ['dump', 'schema' => $schema->getSchemaName()]
                );
                $globalPool->add($pool);
            }

            $dumper = new Dumper(
                $schema->getSchemaConfig(),
                $output,
                new TableDumperFactory($pool, $filesystem, $format, $seedType, $override),
                $filesystem
            );
            $dumper->dump($schema);
        }

        $processTable = new Table($output, $globalPool);
        $processTable->setShowSummary(true);

        if (!$processTable->run(0.1)) {
            return 1;
        }

        return 0;
    }
}
