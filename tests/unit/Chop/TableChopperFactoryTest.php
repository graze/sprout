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

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Chop\Mysql\MysqlTableChopper;
use Graze\Sprout\Chop\TableChopperFactory;
use Graze\Sprout\Chop\TableChopperInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class TableChopperFactoryTest extends TestCase
{
    public function testMysqlReturnsMysqlTableChopper()
    {
        $output = Mockery::mock(OutputInterface::class)->shouldIgnoreMissing();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getDriver')
               ->andReturn('mysql');

        $ChopperFactory = new TableChopperFactory($output);

        $tableChopper = $ChopperFactory->getChopper($config);

        $this->assertInstanceOf(TableChopperInterface::class, $tableChopper);
        $this->assertInstanceOf(MysqlTableChopper::class, $tableChopper);
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

        $ChopperFactory = new TableChopperFactory($output);

        $ChopperFactory->getChopper($config);
    }
}
