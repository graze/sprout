<?php

namespace Graze\Sprout\Test\Unit\Dump;

use Graze\ParallelProcess\Table;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Dump\Mysql\MysqlTableDumper;
use Graze\Sprout\Dump\TableDumperFactory;
use Graze\Sprout\Dump\TableDumperInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;

class TableDumperFactoryTest extends TestCase
{
    public function testMysqlReturnsMysqlTableDumper()
    {
        $processTable = Mockery::mock(Table::class);

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $dumperFactory = new TableDumperFactory($processTable);

        $tableDumper = $dumperFactory->getDumper($config);

        $this->assertInstanceOf(TableDumperInterface::class, $tableDumper);
        $this->assertInstanceOf(MysqlTableDumper::class, $tableDumper);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownThrowsException()
    {
        $processTable = Mockery::mock(Table::class);

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('pgsql');

        $dumperFactory = new TableDumperFactory($processTable);

        $dumperFactory->getDumper($config);
    }
}
