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
use Graze\Sprout\Db\Mysql\MysqlTableSeeder;
use Graze\Sprout\Test\TestCase;
use League\Flysystem\AdapterInterface;
use Mockery;
use Symfony\Component\Process\Process;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MysqlTableSeederTest extends TestCase
{
    public function testSeed()
    {
        $process = Mockery::mock('overload:' . Process::class);

        $process->shouldReceive('setCommandLine')
                ->with('(echo \'SET AUTOCOMMIT=0; SET FOREIGN_KEY_CHECKS=0;\'; cat \'some-file\'; echo \'SET AUTOCOMMIT=1; SET FOREIGN_KEY_CHECKS=1;\') | mysql -h\'some-host\' -u\'some-user\' -p\'some-pass\' --max_allowed_packet=512M --default-character-set=utf8 \'some-schema\'')
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
                 ['seed', 'schema' => 'some-schema', 'table' => 'some-table']
             );

        $fileSystem = Mockery::mock(AdapterInterface::class);
        $fileSystem->allows()
                   ->has('some-file')
                   ->andReturns(true);

        $tableSeeder = new MysqlTableSeeder($pool, $config, $fileSystem);

        $tableSeeder->seed('some-file', 'some-schema', 'some-table');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFileExistsFailure()
    {
        $config = Mockery::mock(ConnectionConfigInterface::class);
        $pool = Mockery::mock(Pool::class);
        $fileSystem = Mockery::mock(AdapterInterface::class);
        $fileSystem->allows()
                   ->has('some-file')
                   ->andReturns(false);

        $tableSeeder = new MysqlTableSeeder($pool, $config, $fileSystem);

        $tableSeeder->seed('some-file', 'some-schema', 'some-table');
    }
}
