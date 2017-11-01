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

namespace Graze\Sprout\Test\Unit\Seed;

use Graze\ParallelProcess\Table;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Seed\Mysql\MysqlTableSeeder;
use Graze\Sprout\Seed\TableSeederFactory;
use Graze\Sprout\Seed\TableSeederInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;

class TableSeederFactoryTest extends TestCase
{
    public function testMysqlReturnsMysqlTableSeeder()
    {
        $processTable = Mockery::mock(Table::class);

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $seederFactory = new TableSeederFactory($processTable);

        $tableSeeder = $seederFactory->getSeeder($config);

        $this->assertInstanceOf(TableSeederInterface::class, $tableSeeder);
        $this->assertInstanceOf(MysqlTableSeeder::class, $tableSeeder);
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

        $seederFactory = new TableSeederFactory($processTable);

        $seederFactory->getSeeder($config);
    }
}
