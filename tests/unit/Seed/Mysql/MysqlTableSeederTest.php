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

namespace Graze\Sprout\Seed\Mysql;

use Graze\ParallelProcess\Table;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Process\Process;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MysqlTableSeederTest extends TestCase
{
    // our standards don't handle sub functions properly, until this is fixed
    // @codingStandardsIgnoreStart
    public function testSeed()
    {
        $process = Mockery::mock('overload:' . Process::class);

        $process->shouldReceive('setCommandLine')
                ->with('mysql -h\'some-host\' -u\'some-user\' -p\'some-pass\' --default-character-set=utf8 \'some-schema\' < \'some-file\'')
                ->once();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getHost')
               ->andReturn('some-host');
        $config->shouldReceive('getUser')
               ->andReturn('some-user');
        $config->shouldReceive('getPassword')
               ->andReturn('some-pass');

        $pool = Mockery::mock(Table::class);

        $pool->shouldReceive('add')
             ->with(
                 Mockery::type(Process::class),
                 ['schema' => 'some-schema', 'table' => 'some-table']
             );

        $tableSeeder = new MysqlTableSeeder($pool, $config);

        /**
         * @param string $file
         *
         * @return bool
         */
        function file_exists($file)
        {
            TestCase::assertEquals('some-file', $file);
            return true;
        }

        $tableSeeder->seed('some-file', 'some-schema', 'some-table');
    }
    // @codingStandardsIgnoreEnd

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFileExistsFailure()
    {
        $config = Mockery::mock(ConnectionConfigInterface::class);
        $pool = Mockery::mock(Table::class);

        $tableSeeder = new MysqlTableSeeder($pool, $config);

        // @codingStandardsIgnoreStart
        /**
         * @param string $file
         *
         * @return bool
         */
        function file_exists($file)
        {
            TestCase::assertEquals('some-file', $file);
            return false;
        }
        // @codingStandardsIgnoreEnd

        $tableSeeder->seed('some-file', 'some-schema', 'some-table');
    }
}
