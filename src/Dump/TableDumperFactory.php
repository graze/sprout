<?php

namespace Graze\Sprout\Dump;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Dump\Mysql\MysqlTableDumper;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

class TableDumperFactory
{
    /** @var OutputInterface */
    private $output;

    /**
     * TableDumperFactory constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param ConnectionConfigInterface $connection
     *
     * @return TableDumperInterface
     */
    public function getDumper(ConnectionConfigInterface $connection): TableDumperInterface
    {
        $driver = $connection->getDriver();

        switch ($driver) {
            case 'mysql':
                $this->output->writeln(
                    "Using mysql table dumper for driver: {$driver}",
                    OutputInterface::VERBOSITY_DEBUG
                );
                return new MysqlTableDumper($connection, $this->output);
            default:
                throw new InvalidArgumentException("getDumper: no dumper found for driver: `{$driver}`");
        }
    }
}
