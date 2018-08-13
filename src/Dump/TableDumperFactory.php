<?php

namespace Graze\Sprout\Dump;

use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\Table;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Dump\Mysql\MysqlTableDumper;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TableDumperFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Pool */
    private $processPool;

    /**
     * TableDumperFactory constructor.
     *
     * @param Pool $processPool
     */
    public function __construct(Pool $processPool)
    {
        $this->processPool = $processPool;
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
                if ($this->logger) {
                    $this->logger->debug(
                        "getDumper: using mysql dumper for driver: {$driver}",
                        ['driver' => $driver]
                    );
                }
                return new MysqlTableDumper($this->processPool, $connection);
            default:
                throw new InvalidArgumentException("getDumper: no dumper found for driver: `{$driver}`");
        }
    }
}