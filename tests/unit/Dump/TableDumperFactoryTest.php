<?php

namespace Graze\Sprout\Test\Unit\Dump;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Dump\Mysql\MysqlTableDumper;
use Graze\Sprout\Dump\TableDumperFactory;
use Graze\Sprout\Dump\TableDumperInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class TableDumperFactoryTest extends TestCase
{
    public function testMysqlReturnsMysqlTableDumper()
    {
        $output = Mockery::mock(OutputInterface::class)->shouldIgnoreMissing();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
            ->andReturn('mysql');

        $dumperFactory = new TableDumperFactory($output);

        $tableDumper = $dumperFactory->getDumper($config);

        $this->assertInstanceOf(TableDumperInterface::class, $tableDumper);
        $this->assertInstanceOf(MysqlTableDumper::class, $tableDumper);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownThrowsException()
    {
        $output = Mockery::mock(OutputInterface::class)->shouldIgnoreMissing();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('pgsql');

        $dumperFactory = new TableDumperFactory($output);

        $dumperFactory->getDumper($config);
    }
}
