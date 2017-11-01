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

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Seed\Mysql\MysqlTableSeeder;
use Graze\Sprout\Seed\TableSeederFactory;
use Graze\Sprout\Seed\TableChopperInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class TableSeederFactoryTest extends TestCase
{
    public function testMysqlReturnsMysqlTableSeeder()
    {
        $output = Mockery::mock(OutputInterface::class)->shouldIgnoreMissing();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $SeederFactory = new TableSeederFactory($output);

        $tableSeeder = $SeederFactory->getSeeder($config);

        $this->assertInstanceOf(TableChopperInterface::class, $tableSeeder);
        $this->assertInstanceOf(MysqlTableSeeder::class, $tableSeeder);
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

        $SeederFactory = new TableSeederFactory($output);

        $SeederFactory->getSeeder($config);
    }
}
