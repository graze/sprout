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

namespace Graze\Sprout\Test\Unit\Seed;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Seed\Seeder;
use Graze\Sprout\Seed\TableSeederFactory;
use Graze\Sprout\Seed\TableSeederInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class SeederTest extends TestCase
{
    /**
     * @dataProvider dataTables
     *
     * @param array $tables
     */
    public function testSeederCallsSeedForAllTables(array $tables = [])
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getConnection')
               ->andReturn($connConfig);
        $config->shouldReceive('getSchema')
               ->andReturn('schema');

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln');
        $output->shouldReceive('write');
        $output->shouldReceive('isDecorated')->andReturn(false);
        $output->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_QUIET);

        $factory = Mockery::mock(TableSeederFactory::class);

        $seeder = new Seeder($config, $output, $factory);

        $tableSeeder = Mockery::mock(TableSeederInterface::class);

        $factory->shouldReceive('getSeeder')->with($connConfig)->andReturn($tableSeeder);

        foreach ($tables as $table) {
            $tableSeeder->shouldReceive('seed')
                        ->with("/some/path/schema/{$table}.sql", 'schema', $table)
                        ->once();
        }

        $seeder->Seed('/some/path/schema', $tables);
    }

    /**
     * @return array
     */
    public function dataTables()
    {
        return [
            [['table1', 'table2', 'table3']],
            [['table1']],
        ];
    }

    public function testSeederIgnoresDuplicateTables()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getConnection')
               ->andReturn($connConfig);
        $config->shouldReceive('getSchema')
               ->andReturn('schema');

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln');
        $output->shouldReceive('write');
        $output->shouldReceive('isDecorated')->andReturn(false);
        $output->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_QUIET);

        $factory = Mockery::mock(TableSeederFactory::class);

        $seeder = new Seeder($config, $output, $factory);

        $tableSeeder = Mockery::mock(TableSeederInterface::class);

        $factory->shouldReceive('getSeeder')->with($connConfig)->andReturn($tableSeeder);

        $tables = ['table1', 'table1'];
        $tableSeeder->shouldReceive('seed')
                    ->with("/some/path/schema/table1.sql", 'schema', 'table1')
                    ->once();
        $seeder->seed('/some/path/schema', $tables);
    }

    public function testSeederDoesNothingWithAnEmptyListOfTables()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getConnection')
               ->andReturn($connConfig);
        $config->shouldReceive('getSchema')
               ->andReturn('schema');

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->with('<warning>No tables specified, nothing to do</warning>')->once();

        $factory = Mockery::mock(TableSeederFactory::class);

        $seeder = new Seeder($config, $output, $factory);

        $tableSeeder = Mockery::mock(TableSeederInterface::class);
        $factory->shouldReceive('getSeeder')->with($connConfig)->andReturn($tableSeeder);

        $tables = [];
        $seeder->seed('/some/path/schema', $tables);
    }
}
