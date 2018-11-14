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

use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\Table;
use Graze\Sprout\Chop\Chopper;
use Graze\Sprout\Chop\TableChopperFactory;
use Graze\Sprout\Config\Config;
use Graze\Sprout\Db\DbTablePopulator;
use Graze\Sprout\Parser\FileTablePopulator;
use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Parser\SchemaParser;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChopCommand extends Command
{
    const OPTION_GROUP  = 'group';
    const OPTION_CONFIG = 'config';
    const OPTION_ALL    = 'all';

    const ARGUMENT_SCHEMA_TABLES = 'schemaTables';

    protected function configure()
    {
        $this->setName('chop');
        $this->setAliases(['truncate']);
        $this->setDescription('Chop down (truncate) all the tables');

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
            'The group to truncate'
        );

        $this->addOption(
            static::OPTION_ALL,
            'a',
            InputOption::VALUE_NONE,
            'Truncate all the tables in the schemas, '
            . 'if you specify tables in the <schemaTables> only those will be used. '
            . 'If this is not supplied only the files with seed data will be truncated'
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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemas = $input->getArgument(static::ARGUMENT_SCHEMA_TABLES);
        $config = (new Config())->parse($input->getOption('config'));
        $group = $input->getOption('group') ?: $config->get(Config::CONFIG_DEFAULT_GROUP);

        $tablePopulator = $input->getOption(static::OPTION_ALL)
            ? new DbTablePopulator()
            : new FileTablePopulator(new Local('/'));
        $schemaParser = new SchemaParser($tablePopulator, $config, $group);
        $parsedSchemas = $schemaParser->extractSchemas($schemas);

        $useGlobal = count($parsedSchemas) <= 10;

        $globalPool = new Pool();
        $globalPool->setMaxSimultaneous($config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES));

        foreach ($parsedSchemas as $schema) {
            if ($useGlobal) {
                $pool = $globalPool;
            } else {
                $pool = new Pool(
                    [],
                    $config->get(Config::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES),
                    false,
                    ['chop', 'schema' => $schema->getSchemaName()]
                );
                $globalPool->add($pool);
            }

            $chopper = new Chopper($schema->getSchemaConfig(), $output, new TableChopperFactory($pool));
            $chopper->chop($schema->getTables());
        }

        $processTable = new Table($output, $globalPool);
        $processTable->setShowSummary(true);

        if (!$processTable->run(0.1)) {
            return 1;
        }

        return 0;
    }
}
