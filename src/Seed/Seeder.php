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

namespace Graze\Sprout\Seed;

use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Db\Schema;
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
     * @param Schema $schema
     */
    public function seed(Schema $schema)
    {
        if (count($schema->getTables()) === 0) {
            $this->output->writeln('<warning>No tables specified, nothing to do</warning>');
            return;
        }

        foreach ($schema->getTables() as $table) {
            $tableSeeder = $this->factory->getSeeder($schema, $table);
            $tableSeeder->seed($schema, $table);
        }
    }
}
