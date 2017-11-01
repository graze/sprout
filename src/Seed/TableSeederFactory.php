<?php

namespace Graze\Sprout\Seed;

use Graze\ParallelProcess\Table;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Seed\Mysql\MysqlTableSeeder;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TableSeederFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Table */
    private $processPool;

    /**
     * TableDumperFactory constructor.
     *
     * @param Table $processPool
     */
    public function __construct(Table $processPool)
    {
        $this->processPool = $processPool;
    }

    /**
     * @param ConnectionConfigInterface $connection
     *
     * @return TableSeederInterface
     */
    public function getSeeder(ConnectionConfigInterface $connection): TableSeederInterface
    {
        $driver = $connection->getDriver();

        switch ($driver) {
            case 'mysql':
                if ($this->logger) {
                    $this->logger->debug(
                        "getSeeder: using mysql seeder for driver: {$driver}",
                        ['driver' => $driver]
                    );
                }
                return new MysqlTableSeeder($this->processPool, $connection);
            default:
                throw new InvalidArgumentException("getSeeder: no seeder found for driver: `{$driver}`");
        }
    }
}
