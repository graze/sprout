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

namespace Graze\Sprout\Seed;

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Db\Mysql\MysqlTableSeeder;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\File\Format;
use Graze\Sprout\File\Php\FileTableSeeder;
use Graze\Sprout\File\Php\PhpTableSeeder;
use Graze\Sprout\File\Reader\CsvReader;
use Graze\Sprout\File\Reader\JsonReader;
use Graze\Sprout\File\Reader\YamlReader;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TableSeederFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Pool */
    private $processPool;
    /** @var AdapterInterface */
    private $filesystem;
    /** @var TableSeederInterface[] */
    private $seeders;

    /**
     * TableDumperFactory constructor.
     *
     * @param Pool             $processPool
     * @param AdapterInterface $filesystem
     */
    public function __construct(Pool $processPool, AdapterInterface $filesystem)
    {
        $this->processPool = $processPool;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string   $driver
     * @param string   $format
     * @param callable $generator
     *
     * @return TableSeederInterface
     */
    private function generate(string $driver, string $format, callable $generator)
    {
        $hash = $driver . '.' . $format;
        if (!isset($this->dumpers[$hash])) {
            $this->seeders[$hash] = $generator();
        }
        return $this->seeders[$hash];
    }

    /**
     * @param Schema $schema
     * @param Table  $table
     *
     * @return TableSeederInterface
     */
    public function getSeeder(Schema $schema, Table $table): TableSeederInterface
    {
        $connection = $schema->getSchemaConfig()->getConnection();
        $extension = $table->getPath() ? pathinfo($table->getPath(), PATHINFO_EXTENSION) : Format::TYPE_SQL;
        $format = Format::parseFormat($extension);

        switch (true) {
            case ($connection->getDriver() == ConnectionConfigInterface::DRIVER_MYSQL
                  && $format == Format::TYPE_SQL):
                return $this->generate(
                    $connection->getDriver(),
                    'sql',
                    function () {
                        return new MysqlTableSeeder($this->processPool, $this->filesystem);
                    }
                );
            case $format == Format::TYPE_PHP:
                return $this->generate(
                    '',
                    Format::TYPE_PHP,
                    function () {
                        return new PhpTableSeeder($this->processPool, $this->filesystem);
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
                        return new FileTableSeeder(
                            $reader,
                            $this->processPool,
                            $this->filesystem
                        );
                    }
                );
            default:
                throw new InvalidArgumentException("no dumper could be generated for driver:`{$connection->getDriver()}` and fileType:`{$extension}`");
        }
    }
}
