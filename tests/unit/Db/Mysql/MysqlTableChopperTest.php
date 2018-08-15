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

namespace Graze\Sprout\Test\Unit\Db\Mysql;

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Db\Mysql\MysqlTableChopper;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Process\Process;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MysqlTableChopperTest extends TestCase
{
    public function testChop()
    {
        $process = Mockery::mock('overload:' . Process::class);

        $process->shouldReceive('setCommandLine')
                ->with('mysql -h\'some-host\' -u\'some-user\' -p\'some-pass\' --default-character-set=utf8 --execute=\'TRUNCATE `some-table`\' \'some-schema\'')
                ->once();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getHost')
               ->andReturn('some-host');
        $config->shouldReceive('getUser')
               ->andReturn('some-user');
        $config->shouldReceive('getPassword')
               ->andReturn('some-pass');

        $pool = Mockery::mock(Pool::class);

        $pool->shouldReceive('add')
             ->with(
                 Mockery::type(Process::class),
                 ['chop', 'schema' => 'some-schema', 'table' => 'some-table']
             );

        $tableChopper = new MysqlTableChopper($pool, $config);

        $tableChopper->chop('some-schema', 'some-table');
    }
}
