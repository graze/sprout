<?php

namespace Graze\Sprout\Db;

use Graze\Sprout\Config\ConnectionConfigInterface;
use PDO;

class PdoFactory
{
    /**
     * @param ConnectionConfigInterface $connection
     * @param array                     $options
     *
     * @return PDO
     */
    public function getPdo(ConnectionConfigInterface $connection, array $options = [])
    {
        return new PDO(
            $connection->getDsn(),
            $connection->getUser(),
            $connection->getPassword(),
            $options
        );
    }
}
