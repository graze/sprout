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

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Db\Mysql\MysqlTableSeeder;
use Graze\Sprout\Seed\TableSeederFactory;
use Graze\Sprout\Seed\TableSeederInterface;
use Graze\Sprout\Test\TestCase;
use League\Flysystem\AdapterInterface;
use Mockery;
use Psr\Log\LoggerInterface;

class TableSeederFactoryTest extends TestCase
{
    public function testMysqlReturnsMysqlTableSeeder()
    {
        $processTable = Mockery::mock(Pool::class);

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $filesystem = Mockery::mock(AdapterInterface::class);

        $seederFactory = new TableSeederFactory($processTable, $filesystem);

        $tableSeeder = $seederFactory->getSeeder($config);

        $this->assertInstanceOf(TableSeederInterface::class, $tableSeeder);
        $this->assertInstanceOf(MysqlTableSeeder::class, $tableSeeder);
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

        $filesystem = Mockery::mock(AdapterInterface::class);

        $seederFactory = new TableSeederFactory($processTable, $filesystem);

        $seederFactory->getSeeder($config);
    }

    public function testLogging()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $pool = Mockery::mock(Pool::class);
        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $filesystem = Mockery::mock(AdapterInterface::class);

        $seederFactory = new TableSeederFactory($pool, $filesystem);
        $seederFactory->setLogger($logger);

        $logger->allows()
               ->debug(
                   "getSeeder: using mysql seeder for driver: mysql",
                   ['driver' => 'mysql']
               );

        $tableSeeder = $seederFactory->getSeeder($config);

        $this->assertInstanceOf(TableSeederInterface::class, $tableSeeder);
    }
}
