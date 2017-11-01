<?php

namespace Graze\Sprout\Dump\Mysql;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @runTestsInSeparateProcesses
 */
class MysqlTableDumperTest extends TestCase
{
    public function testDump()
    {
        $process = Mockery::mock('overload:' . Process::class);

        $process->shouldReceive('setCommandLine')
                ->with(
                    'mysqldump -h\'some-host\' -u\'some-user\' -p\'some-pass\' --compress --compact --no-create-info' .
                    ' --extended-insert --hex-dump --quick \'some-schema\' \'some-table\'' .
                    '| sed \'s$VALUES ($VALUES\n($g\' | sed \'s$),($),\n($g\' > \'some-file\''
                )
                ->once();

        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->once()->andReturn(true);

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('write')
               ->with('dumping some-schema/some-table to some-file... ', 256)
               ->once();
        $output->shouldReceive('writeln')
               ->with('<info>done</info>', 256)
               ->once();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getHost')
               ->andReturn('some-host');
        $config->shouldReceive('getUser')
               ->andReturn('some-user');
        $config->shouldReceive('getPassword')
               ->andReturn('some-pass');

        $tableDumper = new MysqlTableDumper($config, $output);

        $tableDumper->dump('some-schema', 'some-table', 'some-file');
    }

    /**
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function testFailure()
    {
        $process = Mockery::mock('overload:' . Process::class);

        $process->shouldReceive('setCommandLine')
                ->with(
                    'mysqldump -h\'some-host\' -u\'some-user\' -p\'some-pass\' --compress --compact --no-create-info' .
                    ' --extended-insert --hex-dump --quick \'some-schema\' \'some-table\'' .
                    '| sed \'s$VALUES ($VALUES\n($g\' | sed \'s$),($),\n($g\' > \'some-file\''
                )
                ->once();

        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->once()->andReturn(false);
        $process->shouldReceive('getCommandLine')->andReturn('some command init');
        $process->shouldReceive('getExitCode')->andReturn(5);
        $process->shouldReceive('getExitCodeText')->andReturn('poop');
        $process->shouldReceive('getWorkingDirectory')->andReturn('/tmp');
        $process->shouldReceive('isOutputDisabled')->andReturn(true);

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('write')
               ->with('dumping some-schema/some-table to some-file... ', 256)
               ->once();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getHost')
               ->andReturn('some-host');
        $config->shouldReceive('getUser')
               ->andReturn('some-user');
        $config->shouldReceive('getPassword')
               ->andReturn('some-pass');

        $tableDumper = new MysqlTableDumper($config, $output);

        /**
         * overload unlink to test that we try and delete the file
         *
         * @param string $file
         */
        function unlink($file)
        {
            TestCase::assertEquals('some-file', $file);
        }

        $tableDumper->dump('some-schema', 'some-table', 'some-file');
    }
}
