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

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Seed\TableSeederInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MysqlTableSeeder implements TableSeederInterface
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

    public function seed(string $file, string $schema, string $table)
    {
        $this->output->write("seeding {$file} to {$schema}/{$table}... ", OutputInterface::VERBOSITY_DEBUG);

        if (!file_exists($file)) {
            throw new InvalidArgumentException("seed: The file: {$file} does not exist");
        }

        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                'mysql -h%1$s -u%2$s -p%3$s --default-character-set=utf8 %4$s < %5$s',
                escapeshellarg($this->connection->getHost()),
                escapeshellarg($this->connection->getUser()),
                escapeshellarg($this->connection->getPassword()),
                escapeshellarg($schema),
                escapeshellarg($file)
            )
        );
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->output->writeln("<info>done</info>", OutputInterface::VERBOSITY_DEBUG);
    }
}
