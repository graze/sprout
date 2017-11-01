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

namespace Graze\Sprout\Dump\Mysql;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Dump\TableDumperInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MysqlTableDumper implements TableDumperInterface
{
    /** @var ConnectionConfigInterface */
    private $connection;
    /** @var OutputInterface */
    private $output;

    /**
     * MysqlTableDumper constructor.
     *
     * @param ConnectionConfigInterface $connection
     * @param OutputInterface           $output
     */
    public function __construct(ConnectionConfigInterface $connection, OutputInterface $output)
    {
        $this->connection = $connection;
        $this->output = $output;
    }

    /**
     * @param string $schema
     * @param string $table
     * @param string $file
     */
    public function dump(string $schema, string $table, string $file)
    {
        $this->output->write("dumping {$schema}/{$table} to {$file}... ", OutputInterface::VERBOSITY_DEBUG);

        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                'mysqldump -h%1$s -u%2$s -p%3$s --compress --compact --no-create-info' .
                ' --extended-insert --hex-dump --quick %4$s %5$s' .
                '| sed \'s$VALUES ($VALUES\n($g\' | sed \'s$),($),\n($g\' > %6$s',
                escapeshellarg($this->connection->getHost()),
                escapeshellarg($this->connection->getUser()),
                escapeshellarg($this->connection->getPassword()),
                escapeshellarg($schema),
                escapeshellarg($table),
                escapeshellarg($file)
            )
        );

        $process->run();

        if (!$process->isSuccessful()) {
            unlink($file);
            throw new ProcessFailedException($process);
        }

        $this->output->writeln("<info>done</info>", OutputInterface::VERBOSITY_DEBUG);
    }
}
