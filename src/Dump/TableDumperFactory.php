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

namespace Graze\Sprout\Dump;

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Db\Mysql\MysqlTableDumper;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\File\Format;
use Graze\Sprout\File\Php\FileTableDumper;
use Graze\Sprout\File\Reader\CsvReader;
use Graze\Sprout\File\Reader\JsonReader;
use Graze\Sprout\File\Reader\YamlReader;
use Graze\Sprout\File\Writer\CsvWriter;
use Graze\Sprout\File\Writer\JsonWriter;
use Graze\Sprout\File\Writer\YamlWriter;
use Graze\Sprout\SeedDataInterface;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TableDumperFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Pool */
    private $processPool;
    /** @var TableDumperInterface[] */
    private $dumpers;
    /** @var AdapterInterface */
    private $filesystem;
    /** @var string */
    private $format;
    /** @var string */
    private $seedType;
    /** @var bool */
    private $override;

    /**
     * TableDumperFactory constructor.
     *
     * @param Pool             $processPool
     * @param AdapterInterface $filesystem
     * @param string           $format
     * @param string           $seedType
     * @param bool             $override
     */
    public function __construct(
        Pool $processPool,
        AdapterInterface $filesystem,
        string $format = Format::TYPE_CSV,
        string $seedType = SeedDataInterface::SEED_TYPE_TRUNCATE,
        bool $override = false
    ) {
        $this->processPool = $processPool;
        $this->filesystem = $filesystem;
        $this->format = $format;
        $this->seedType = $seedType;
        $this->override = $override;
    }

    /**
     * @param string   $driver
     * @param string   $extension
     *
     * @param callable $generator
     *
     * @return TableDumperInterface
     */
    private function generate(string $driver, string $extension, callable $generator)
    {
        $hash = $driver . '.' . $extension;
        if (!isset($this->dumpers[$hash])) {
            $this->dumpers[$hash] = $generator();
        }
        return $this->dumpers[$hash];
    }

    /**
     * @param Schema $schema
     * @param Table  $table
     *
     * @return TableDumperInterface
     */
    public function getDumper(Schema $schema, Table $table): TableDumperInterface
    {
        $connection = $schema->getSchemaConfig()->getConnection();
        if ($this->override) {
            $extension = $this->format;
        } else {
            $extension = $table->getPath() ? pathinfo($table->getPath(), PATHINFO_EXTENSION) : $this->format;
        }

        switch (true) {
            case ($connection->getDriver() == ConnectionConfigInterface::DRIVER_MYSQL
                  && $extension == Format::TYPE_SQL):
                return $this->generate(
                    $connection->getDriver(),
                    Format::TYPE_SQL,
                    function () {
                        return new MysqlTableDumper($this->processPool);
                    }
                );
            case $extension == Format::TYPE_CSV:
                $reader = new CsvReader($this->filesystem);
                $writer = new CsvWriter($this->filesystem);
            // fall through
            case $extension == Format::TYPE_YAML:
            case $extension == 'yml':
                $reader = $reader ?? new YamlReader($this->filesystem);
                $writer = $writer ?? new YamlWriter($this->filesystem);
            // fall through
            case $extension == Format::TYPE_JSON:
                $reader = $reader ?? new JsonReader($this->filesystem);
                $writer = $writer ?? new JsonWriter($this->filesystem);
                return $this->generate(
                    '',
                    $extension,
                    function () use ($writer, $reader) {
                        return new FileTableDumper(
                            $writer,
                            $reader,
                            $this->processPool,
                            $this->filesystem,
                            $this->seedType,
                            $this->override
                        );
                    }
                );
            default:
                throw new InvalidArgumentException("no dumper could be generated for driver:`{$connection->getDriver()}` and fileType:`{$extension}`");
        }
    }
}
