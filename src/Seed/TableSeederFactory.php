<?php

namespace Graze\Sprout\Seed;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Seed\Mysql\MysqlTableSeeder;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

class TableSeederFactory
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

    public function getSeeder(ConnectionConfigInterface $connection): TableSeederInterface
    {
        $driver = $connection->getDriver();

        switch ($driver) {
            case 'mysql':
                $this->output->writeln(
                    "Using mysql table seeder for driver: {$driver}",
                    OutputInterface::VERBOSITY_DEBUG
                );
                return new MysqlTableSeeder($connection, $this->output);
            default:
                throw new InvalidArgumentException("getSeeder: no seeder found for driver: `{$driver}`");
        }
    }
}
