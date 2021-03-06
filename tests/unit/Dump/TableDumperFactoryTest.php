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

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Db\Mysql\MysqlTableDumper;
use Graze\Sprout\Dump\TableDumperFactory;
use Graze\Sprout\Dump\TableDumperInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;

class TableDumperFactoryTest extends TestCase
{
    public function testMysqlReturnsMysqlTableDumper()
    {
        $pool = Mockery::mock(Pool::class);

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $dumperFactory = new TableDumperFactory($pool);

        $tableDumper = $dumperFactory->getDumper($config);

        $this->assertInstanceOf(TableDumperInterface::class, $tableDumper);
        $this->assertInstanceOf(MysqlTableDumper::class, $tableDumper);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownThrowsException()
    {
        $pool = Mockery::mock(Pool::class);

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('pgsql');

        $dumperFactory = new TableDumperFactory($pool);

        $dumperFactory->getDumper($config);
    }

    public function testLogging()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $pool = Mockery::mock(Pool::class);
        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $dumperFactory = new TableDumperFactory($pool);
        $dumperFactory->setLogger($logger);

        $logger->allows()
               ->debug(
                   "getDumper: using mysql dumper for driver: mysql",
                   ['driver' => 'mysql']
               );

        $tableDumper = $dumperFactory->getDumper($config);

        $this->assertInstanceOf(TableDumperInterface::class, $tableDumper);
    }
}
