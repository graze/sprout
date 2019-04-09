<?php
/**
 * This file is part of graze/sprout.
 *
 * Copyright © 2018 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/sprout/blob/master/LICENSE.md
 * @link    https://github.com/graze/sprout
 */

namespace Graze\Sprout\Chop;

use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\PoolInterface;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Db\Mysql\MysqlTableChopper;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\SeedData\PhpTableChopper;
use Graze\Sprout\Db\Table;
use Graze\Sprout\File\Format;
use Graze\Sprout\File\Php\FileTableChopper;
use Graze\Sprout\File\Reader\CsvReader;
use Graze\Sprout\File\Reader\JsonReader;
use Graze\Sprout\File\Reader\YamlReader;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TableChopperFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var PoolInterface */
    private $processPool;
    /** @var TableChopperInterface[] */
    private $choppers;
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * TableDumperFactory constructor.
     *
     * @param PoolInterface             $processPool
     * @param AdapterInterface $filesystem
     *
     * @internal param OutputInterface $output
     */
    public function __construct(PoolInterface $processPool, AdapterInterface $filesystem)
    {
        $this->processPool = $processPool;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string   $driver
     * @param string   $format
     * @param callable $generator function (): TableChopperInterface
     *
     * @return TableChopperInterface
     */
    private function generate(string $driver, string $format, callable $generator)
    {
        $hash = $driver . '.' . $format;
        if (!isset($this->choppers[$hash])) {
            $this->choppers[$hash] = $generator();
        }
        return $this->choppers[$hash];
    }

    /**
     * @param Schema $schema
     * @param Table  $table
     *
     * @return TableChopperInterface
     */
    public function getChopper(Schema $schema, Table $table): TableChopperInterface
    {
        $connection = $schema->getSchemaConfig()->getConnection();
        $extension = $table->getPath() ? pathinfo($table->getPath(), PATHINFO_EXTENSION) : Format::TYPE_SQL;
        $format = Format::parseFormat($extension);

        switch (true) {
            case ($connection->getDriver() == ConnectionConfigInterface::DRIVER_MYSQL
                  && $format == Format::TYPE_SQL):
                return $this->generate(
                    $connection->getDriver(),
                    Format::TYPE_SQL,
                    function () {
                        return new MysqlTableChopper($this->processPool);
                    }
                );
            case $format == Format::TYPE_PHP:
                return $this->generate(
                    '',
                    Format::TYPE_PHP,
                    function () {
                        return new PhpTableChopper($this->processPool, $this->filesystem);
                    }
                );
            case $format == Format::TYPE_CSV:
                $reader = new CsvReader($this->filesystem);
            // fall through
            case $format == Format::TYPE_YAML:
                $reader = $reader ?? new YamlReader($this->filesystem);
            // fall through
            case $format == Format::TYPE_JSON:
                $reader = $reader ?? new JsonReader($this->filesystem);
                return $this->generate(
                    '',
                    $format,
                    function () use ($reader) {
                        return new FileTableChopper(
                            $reader,
                            $this->processPool,
                            $this->filesystem
                        );
                    }
                );
            default:
                throw new InvalidArgumentException("no chopper could be generated for driver:`{$connection->getDriver()}` and fileType:`{$extension}`");
        }
    }
}
