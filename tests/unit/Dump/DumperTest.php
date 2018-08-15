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

namespace Graze\Sprout\Test\Unit\Dump;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Dump\Dumper;
use Graze\Sprout\Dump\TableDumperFactory;
use Graze\Sprout\Dump\TableDumperInterface;
use Graze\Sprout\Test\TestCase;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class DumperTest extends TestCase
{
    /** @var Dumper */
    private $dumper;
    /** @var mixed */
    private $config;
    /** @var mixed */
    private $outputter;
    /** @var mixed */
    private $factory;
    /** @var mixed */
    private $filesystem;

    public function setUp()
    {
        $this->config = Mockery::mock(SchemaConfigInterface::class);
        $this->outputter = Mockery::mock(OutputInterface::class);
        $this->factory = Mockery::mock(TableDumperFactory::class);
        $this->filesystem = Mockery::mock(AdapterInterface::class);

        $this->dumper = new Dumper($this->config, $this->outputter, $this->factory, $this->filesystem);
    }

    /**
     * @dataProvider dataTables
     *
     * @param array $tables
     */
    public function testDumperCallsDumpForAllTables(array $tables = [])
    {
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $this->config->shouldReceive('getConnection')
                     ->andReturn($connConfig);
        $this->config->shouldReceive('getSchema')
                     ->andReturn('schema');

        $this->outputter->shouldReceive('writeln');
        $this->outputter->shouldReceive('write');
        $this->outputter->shouldReceive('isDecorated')->andReturn(false);
        $this->outputter->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_QUIET);

        $tableDumper = Mockery::mock(TableDumperInterface::class);

        $this->factory->shouldReceive('getDumper')->with($connConfig)->andReturn($tableDumper);

        $this->filesystem->allows()
                         ->createDir('/some/path/schema', Mockery::type(Config::class))
                         ->andReturns([]);

        foreach ($tables as $table) {
            $tableDumper->shouldReceive('dump')
                        ->with('schema', $table, "/some/path/schema/{$table}.sql")
                        ->once();
        }

        $this->dumper->dump('/some/path/schema', $tables);
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
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $this->config->shouldReceive('getConnection')
                     ->andReturn($connConfig);
        $this->config->shouldReceive('getSchema')
                     ->andReturn('schema');

        $this->outputter->shouldReceive('writeln');
        $this->outputter->shouldReceive('write');
        $this->outputter->shouldReceive('isDecorated')->andReturn(false);
        $this->outputter->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_QUIET);

        $tableDumper = Mockery::mock(TableDumperInterface::class);

        $this->factory->shouldReceive('getDumper')->with($connConfig)->andReturn($tableDumper);

        $this->filesystem->allows()
                         ->createDir('/some/path/schema', Mockery::type(Config::class))
                         ->andReturns([]);

        $tables = ['table1', 'table1'];
        $tableDumper->shouldReceive('dump')
                    ->with('schema', 'table1', "/some/path/schema/table1.sql")
                    ->once();
        $this->dumper->dump('/some/path/schema', $tables);
    }

    public function testDumperDoesNothingWithAnEmptyListOfTables()
    {
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $this->config->shouldReceive('getConnection')
                     ->andReturn($connConfig);
        $this->config->shouldReceive('getSchema')
                     ->andReturn('schema');

        $this->outputter->shouldReceive('writeln')
                        ->with('<warning>No tables specified, nothing to do</warning>')
                        ->once();

        $tableDumper = Mockery::mock(TableDumperInterface::class);
        $this->factory->shouldReceive('getDumper')->with($connConfig)->andReturn($tableDumper);

        $tables = [];
        $this->dumper->dump('/some/path/schema', $tables);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage dump: failed to create directory: /some/path/schema
     */
    public function testDumperThrowsAnExceptionIfUnableToCreateTheDirectory()
    {
        $connConfig = Mockery::mock(ConnectionConfigInterface::class);
        $this->config->shouldReceive('getConnection')
                     ->andReturn($connConfig);
        $this->config->shouldReceive('getSchema')
                     ->andReturn('schema');

        $this->outputter->shouldReceive('writeln');
        $this->outputter->shouldReceive('write');
        $this->outputter->shouldReceive('isDecorated')->andReturn(false);
        $this->outputter->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_QUIET);

        $tableDumper = Mockery::mock(TableDumperInterface::class);

        $this->factory->shouldReceive('getDumper')->with($connConfig)->andReturn($tableDumper);

        $this->filesystem->allows()
                         ->createDir('/some/path/schema', Mockery::type(Config::class))
                         ->andReturns(false);

        $tables = ['table1', 'table1'];
        $this->dumper->dump('/some/path/schema', $tables);
    }
}
