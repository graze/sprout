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

        $file1 = Mockery::mock(\SplFileInfo::class);
        $file1->allows(['getExtension' => 'sql', 'getSize' => 1234]);
        $file1->allows()
              ->getBasename('.sql')
              ->andReturns('table1');

        $file2 = Mockery::mock(\SplFileInfo::class);
        $file2->allows(['getExtension' => 'sql', 'getSize' => 1235]);
        $file2->allows()
              ->getBasename('.sql')
              ->andReturns('table2');

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
