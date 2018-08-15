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

namespace Graze\Sprout\Test\Unit\Db;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Db\DbTablePopulator;
use Graze\Sprout\Db\PdoFactory;
use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Parser\TableFilterer;
use Graze\Sprout\Test\TestCase;
use Mockery;
use PDO;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DbTablePopulatorTest extends TestCase
{
    /** @var DbTablePopulator */
    private $tablePopulator;
    /** @var mixed */
    private $tableFilterer;
    /** @var mixed */
    private $pdoFactory;

    public function setUp()
    {
        $this->pdoFactory = Mockery::mock(PdoFactory::class);
        $this->tableFilterer = Mockery::mock(TableFilterer::class);
        $this->tablePopulator = new DbTablePopulator($this->pdoFactory, $this->tableFilterer);
    }

    public function testTablePopulation()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);
        $connection = Mockery::mock(ConnectionConfigInterface::class);
        $config->allows([
            'getExcludes'   => [],
            'getSchema'     => 'schema1',
            'getConnection' => $connection,
        ]);

        $pdo = Mockery::mock(PDO::class);
        $this->pdoFactory->allows()
                         ->getPdo($connection)
                         ->andReturns($pdo);
        $statement = Mockery::mock(\PDOStatement::class);
        $pdo->allows()
            ->prepare(
                'SELECT table_name
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE table_schema = :schema
                AND table_type = "BASE TABLE"'
            )
            ->andReturns($statement);
        $statement->allows()
                  ->execute(['schema' => 'schema1'])
                  ->andReturns($statement);
        $statement->allows()
                  ->fetchColumn(0)
                  ->andReturns(['table1', 'table2']);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);

        $this->assertEquals(['table1', 'table2'], $output->getTables());
    }

    public function testNoTablesArePopulatedIfTheyAreAlreadyProvided()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', ['table1', 'table2']);
        $config->allows(['getExcludes' => []]);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);

        $this->assertEquals(['table1', 'table2'], $output->getTables());
    }

    public function testTablesAreExcludedIfIncludedInExcludeList()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', ['table1', 'table2']);
        $config->allows(['getExcludes' => ['table1']]);

        $this->tableFilterer->allows()
                            ->filter(['table1', 'table2'], ['table1'])
                            ->andReturns(['table2']);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);

        $this->assertEquals(['table2'], $output->getTables());
    }

    public function testTablesAreFilteredWhenPopulatedFromADatabase()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);
        $connection = Mockery::mock(ConnectionConfigInterface::class);
        $config->allows([
            'getExcludes'   => ['table1'],
            'getSchema'     => 'schema1',
            'getConnection' => $connection,
        ]);

        $pdo = Mockery::mock(PDO::class);
        $this->pdoFactory->allows()
                         ->getPdo($connection)
                         ->andReturns($pdo);
        $statement = Mockery::mock(\PDOStatement::class);
        $pdo->allows()
            ->prepare(
                'SELECT table_name
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE table_schema = :schema
                AND table_type = "BASE TABLE"'
            )
            ->andReturns($statement);
        $statement->allows()
                  ->execute(['schema' => 'schema1'])
                  ->andReturns($statement);
        $statement->allows()
                  ->fetchColumn(0)
                  ->andReturns(['table1', 'table2']);

        $this->tableFilterer->allows()
                            ->filter(['table1', 'table2'], ['table1'])
                            ->andReturns(['table2']);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);

        $this->assertEquals(['table2'], $output->getTables());
    }
}
