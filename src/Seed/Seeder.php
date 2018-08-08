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

namespace Graze\Sprout\Seed;

use Graze\Sprout\Config\SchemaConfigInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Seeder
{
    /** @var SchemaConfigInterface */
    private $schemaConfig;
    /** @var OutputInterface */
    private $output;
    /** @var TableSeederFactory */
    private $factory;

    /**
     * Dumper constructor.
     *
     * @param SchemaConfigInterface $schemaConfig
     * @param OutputInterface       $output
     * @param TableSeederFactory    $factory
     */
    public function __construct(
        SchemaConfigInterface $schemaConfig,
        OutputInterface $output,
        TableSeederFactory $factory
    ) {
        $this->schemaConfig = $schemaConfig;
        $this->output = $output;
        $this->factory = $factory;
    }

    /**
     * Seed a collection of files to tables
     *
     * @param string   $path
     * @param string[] $tables
     */
    public function seed(string $path, array $tables = [])
    {
        $tables = array_unique($tables);

        if (count($tables) === 0) {
            $this->output->writeln('<warning>No tables specified, nothing to do</warning>');
            return;
        }

        $tableSeeder = $this->factory->getSeeder($this->schemaConfig->getConnection());
        $schema = $this->schemaConfig->getSchema();

        foreach ($tables as $table) {
            $file = sprintf('%s/%s.sql', $path, $table);
            $tableSeeder->seed($file, $schema, $table);
        }
    }
}
