<?php

namespace Graze\Sprout\Dump\Mysql;

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Process\Process;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MysqlTableDumperTest extends TestCase
{
    public function testDump()
    {
        $process = Mockery::mock('overload:' . Process::class);

        $process->shouldReceive('setCommandLine')
                ->with(
                    'mysqldump -h\'some-host\' -u\'some-user\' -p\'some-pass\' --compress --compact --no-create-info' .
                    ' --extended-insert --hex-blob --quick --complete-insert \'some-schema\' \'some-table\' ' .
                    '| process-mysqldump > \'some-file\''
                )
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
                 ['dump', 'schema' => 'some-schema', 'table' => 'some-table']
             );

        $tableDumper = new MysqlTableDumper($pool, $config);

        $tableDumper->dump('some-schema', 'some-table', 'some-file');
    }
}
