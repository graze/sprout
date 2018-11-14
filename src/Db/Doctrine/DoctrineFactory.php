<?php

namespace Graze\Sprout\Db\Doctrine;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Db\DbFactoryInterface;
use Graze\Sprout\Db\DbInterface;

class DoctrineFactory implements DbFactoryInterface
{
    /** @var DbInterface[] */
    private $dbs = [];

    /**
     * @param ConnectionConfigInterface $connection
     *
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getDoctrine(ConnectionConfigInterface $connection)
    {
        $connectionParams = ['url' => $connection->getUrl()];
        return DriverManager::getConnection($connectionParams, new Configuration());
    }

    /**
     * Get a DB connection from a schema
     *
     * @param SchemaConfigInterface $schema
     *
     * @return DbInterface
     */
    public function getDb(SchemaConfigInterface $schema): DbInterface
    {
        if (!isset($this->dbs[$schema->getSchema()])) {
            try {
                $this->dbs[$schema->getSchema()] = new DoctrineDb(
                    $this->getDoctrine($schema->getConnection())
                );
            } catch (DBALException $e) {
                throw new \RuntimeException(
                    "get database: failed to connect to database for schema: {$schema->getSchema()}",
                    0,
                    $e
                );
            }
            if (!$this->dbs[$schema->getSchema()] instanceof DbInterface) {
                throw new \InvalidArgumentException("Unable to create database connection");
            }
        }

        return $this->dbs[$schema->getSchema()];
    }
}
