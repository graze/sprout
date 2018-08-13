<?php

namespace Graze\Sprout\Test\Unit\Parser;

use Graze\Sprout\Config;
use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Parser\SchemaParser;
use Graze\Sprout\Parser\TablePopulator;
use Graze\Sprout\Test\TestCase;
use League\Flysystem\AdapterInterface;
use Mockery;

class TablePopulatorTest extends TestCase
{
    /** @var TablePopulator */
    private $schemaParser;
    /** @var mixed */
    private $fileSystem;
    /** @var mixed */
    private $config;

    public function setUp()
    {
        $this->fileSystem = Mockery::mock(AdapterInterface::class);
        $this->schemaParser = new TablePopulator($this->fileSystem);
    }

    public function testPopulateTablesWithExistingTablesDoesNothing()
    {
        $config = Mockery::mock(Config\SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', ['table1', 'table2']);

        $output = $this->schemaParser->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);

        $this->assertEquals(['table1', 'table2'], $parsedSchema->getTables());
    }

    public function testPopulateTablesWithNoTablesWillSearchTheFilesystemForTables()
    {
        $config = Mockery::mock(Config\SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);

        $this->fileSystem->allows()
                         ->has('/a/path')
                         ->andReturns(true);

        $file1 = ['path' => '/a/path/table1.sql', 'size' => 1234];
        $file2 = ['path' => '/a/path/table2.sql', 'size' => 1234];

        $this->fileSystem->allows()
                         ->listContents('/a/path')
                         ->andReturns([$file1, $file2]);

        $output = $this->schemaParser->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);
    }

    public function testPopulateTablesWhenFolderDoesNotExistReturnsNull()
    {
        $config = Mockery::mock(Config\SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);

        $this->fileSystem->allows()
                         ->has('/a/path')
                         ->andReturns(false);

        $output = $this->schemaParser->populateTables($parsedSchema);

        $this->assertNull($output);
    }

    public function testPopulateTablesWithNoFilesReturnsNull()
    {
        $config = Mockery::mock(Config\SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);

        $this->fileSystem->allows()
                         ->has('/a/path')
                         ->andReturns(true);

        $this->fileSystem->allows()
                         ->listContents('/a/path')
                         ->andReturns([]);

        $output = $this->schemaParser->populateTables($parsedSchema);

        $this->assertNull($output);
    }
}
