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

namespace Graze\Sprout\Test\Unit\Chop;

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Chop\Mysql\MysqlTableChopper;
use Graze\Sprout\Chop\TableChopperFactory;
use Graze\Sprout\Chop\TableChopperInterface;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;

class TableChopperFactoryTest extends TestCase
{
    public function testMysqlReturnsMysqlTableChopper()
    {
        $processTable = Mockery::mock(Pool::class);

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $chopperFactory = new TableChopperFactory($processTable);

        $tableChopper = $chopperFactory->getChopper($config);

        $this->assertInstanceOf(TableChopperInterface::class, $tableChopper);
        $this->assertInstanceOf(MysqlTableChopper::class, $tableChopper);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownThrowsException()
    {
        $processTable = Mockery::mock(Pool::class);

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('pgsql');

        $chopperFactory = new TableChopperFactory($processTable);

        $chopperFactory->getChopper($config);
    }

    public function testLogging()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $pool = Mockery::mock(Pool::class);
        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $chopperFactory = new TableChopperFactory($pool);
        $chopperFactory->setLogger($logger);

        $logger->allows()
               ->debug(
                   "getChopper: using mysql chopper for driver: mysql",
                   ['driver' => 'mysql']
               );

        $tableChopper = $chopperFactory->getChopper($config);

        $this->assertInstanceOf(TableChopperInterface::class, $tableChopper);
    }
}
