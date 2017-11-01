<?php

namespace Graze\Sprout\Test\Unit\Dump;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Dump\Dumper;
use Graze\Sprout\Dump\TableDumperFactory;
use Graze\Sprout\Dump\TableDumperInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class DumperTest extends TestCase
{
    /**
     * @dataProvider dataTables
     *
     * @param array $tables
     */
    public function testDumperCallsDumpForAllTables(array $tables = [])
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

        $factory = Mockery::mock(TableDumperFactory::class);

        $dumper = new Dumper($config, $output, $factory);

        $tableDumper = Mockery::mock(TableDumperInterface::class);

        $factory->shouldReceive('getDumper')->with($connConfig)->andReturn($tableDumper);

        foreach ($tables as $table) {
            $tableDumper->shouldReceive('dump')
                        ->with('schema', $table, "/some/path/schema/{$table}.sql")
                        ->once();
        }

        $dumper->dump('/some/path/schema', $tables);
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

    public function testDumperIgnoresDuplicateTables()
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

        $factory = Mockery::mock(TableDumperFactory::class);

        $dumper = new Dumper($config, $output, $factory);

        $tableDumper = Mockery::mock(TableDumperInterface::class);

        $factory->shouldReceive('getDumper')->with($connConfig)->andReturn($tableDumper);

        $tables = ['table1', 'table1'];
        $tableDumper->shouldReceive('dump')
                    ->with('schema', 'table1', "/some/path/schema/table1.sql")
                    ->once();
        $dumper->dump('/some/path/schema', $tables);
    }

    public function testDumperDoesNothingWithAnEmptyListOfTables()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getConnection')
               ->andReturn($connConfig);
        $config->shouldReceive('getSchema')
               ->andReturn('schema');

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->with('<warning>No tables specified, nothing to do</warning>')->once();

        $factory = Mockery::mock(TableDumperFactory::class);

        $dumper = new Dumper($config, $output, $factory);

        $tableDumper = Mockery::mock(TableDumperInterface::class);
        $factory->shouldReceive('getDumper')->with($connConfig)->andReturn($tableDumper);

        $tables = [];
        $dumper->dump('/some/path/schema', $tables);
    }
}
