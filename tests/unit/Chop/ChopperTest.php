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

namespace Graze\Sprout\Test\Unit\Chop;

use Graze\Sprout\Chop\Chopper;
use Graze\Sprout\Chop\TableChopperFactory;
use Graze\Sprout\Chop\TableChopperInterface;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class ChopperTest extends TestCase
{
    /**
     * @dataProvider dataTables
     *
     * @param array $tables
     */
    public function testChopperCallsChopForAllTables(array $tables = [])
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

        $factory = Mockery::mock(TableChopperFactory::class);

        $chopper = new Chopper($config, $output, $factory);

        $tableChopper = Mockery::mock(TableChopperInterface::class);

        $factory->shouldReceive('getChopper')->with($connConfig)->andReturn($tableChopper);

        foreach ($tables as $table) {
            $tableChopper->shouldReceive('chop')
                         ->with('schema', $table)
                         ->once();
        }

        $chopper->chop($tables);
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

    public function testChopperIgnoresDuplicateTables()
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

        $factory = Mockery::mock(TableChopperFactory::class);

        $chopper = new Chopper($config, $output, $factory);

        $tableChopper = Mockery::mock(TableChopperInterface::class);

        $factory->shouldReceive('getChopper')->with($connConfig)->andReturn($tableChopper);

        $tables = ['table1', 'table1'];
        $tableChopper->shouldReceive('chop')
                     ->with('schema', 'table1')
                     ->once();
        $chopper->chop($tables);
    }

    public function testChopperDoesNothingWithAnEmptyListOfTables()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getConnection')
               ->andReturn($connConfig);
        $config->shouldReceive('getSchema')
               ->andReturn('schema');

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->with('<warning>No tables specified, nothing to do</warning>')->once();

        $factory = Mockery::mock(TableChopperFactory::class);

        $chopper = new Chopper($config, $output, $factory);

        $tableChopper = Mockery::mock(TableChopperInterface::class);
        $factory->shouldReceive('getChopper')->with($connConfig)->andReturn($tableChopper);

        $tables = [];
        $chopper->chop($tables);
    }
}
