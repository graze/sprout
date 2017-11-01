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

namespace Graze\Sprout\Chop\Mysql;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @runTestsInSeparateProcesses
 */
class MysqlTableChopperTest extends TestCase
{
    public function testChop()
    {
        $process = Mockery::mock('overload:' . Process::class);

        $process->shouldReceive('setCommandLine')
                ->with('mysql -h\'some-host\' -u\'some-user\' -p\'some-pass\' --default-character-set=utf8 \'some-schema\' < \'TRUNCATE `some-table`\'')
                ->once();

        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->once()->andReturn(true);

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('write')
               ->with('chopping down some-schema/some-table... ', 256)
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

        $tableChopper = new MysqlTableChopper($config, $output);

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

        $tableChopper->chop('some-file', 'some-schema', 'some-table');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFileExistsFailure()
    {
        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('write')
               ->with('chopping down some-schema/some-table... ', 256)
               ->once();

        $config = Mockery::mock(ConnectionConfigInterface::class);

        $tableChopper = new MysqlTableChopper($config, $output);

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

        $tableChopper->chop('some-file', 'some-schema', 'some-table');
    }

    /**
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function testFailure()
    {
        $process = Mockery::mock('overload:' . Process::class);

        $process->shouldReceive('setCommandLine')
                ->with('mysql -h\'some-host\' -u\'some-user\' -p\'some-pass\' --default-character-set=utf8 \'some-schema\' < \'TRUNCATE `some-table`\'')
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
               ->with('chopping down some-schema/some-table... ', 256)
               ->once();

        $config = Mockery::mock(ConnectionConfigInterface::class);
        $config->shouldReceive('getHost')
               ->andReturn('some-host');
        $config->shouldReceive('getUser')
               ->andReturn('some-user');
        $config->shouldReceive('getPassword')
               ->andReturn('some-pass');

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

        $tableChopper = new MysqlTableChopper($config, $output);

        /**
         * overload unlink to test that we try and delete the file
         *
         * @param string $file
         */
        function unlink($file)
        {
            TestCase::assertEquals('some-file', $file);
        }

        $tableChopper->chop('some-file', 'some-schema', 'some-table');
    }
}
