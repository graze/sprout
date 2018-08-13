<?php

namespace Graze\Sprout\Test\Unit\Parser;

use Graze\Sprout\Config\Config;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Parser\SchemaParser;
use Graze\Sprout\Parser\TablePopulator;
use Graze\Sprout\Test\TestCase;
use Mockery;

class SchemaParserTest extends TestCase
{
    /** @var mixed */
    private $tablePopulator;
    /** @var mixed */
    private $config;
    /** @var SchemaParser */
    private $schemaParser;

    public function setUp()
    {
        $this->tablePopulator = Mockery::mock(TablePopulator::class);
        $this->config = Mockery::mock(Config::class);
        $this->schemaParser = new SchemaParser(
            $this->tablePopulator,
            $this->config,
            'group'
        );
    }

    public function testExtractSchemasWithSchemasAndTablesReturnsTheInput()
    {
        $schema1 = Mockery::mock(SchemaConfigInterface::class);
        $schema2 = Mockery::mock(SchemaConfigInterface::class);
        $this->config->allows()
                     ->getSchemaConfiguration('schema1')
                     ->andReturns($schema1);
        $this->config->allows()
                     ->getSchemaPath($schema1, 'group')
                     ->andReturns('/a/path/to/group/schema1');
        $schema1->allows(['getSchema' => 'schema1']);

        $this->config->allows()
                     ->getSchemaConfiguration('schema2')
                     ->andReturns($schema2);
        $this->config->allows()
                     ->getSchemaPath($schema2, 'group')
                     ->andReturns('/a/path/to/group/schema2');
        $schema2->allows(['getSchema' => 'schema2']);

        $parsedSchemas = [];
        $this->tablePopulator->allows()
                             ->populateTables(Mockery::on(function (ParsedSchema $schema) use (&$parsedSchemas) {
                                 $parsedSchemas[] = $schema;
                                 return true;
                             }))
                             ->andReturnUsing(function () use (&$parsedSchemas) {
                                 return end($parsedSchemas);
                             });

        $output = $this->schemaParser->extractSchemas(['schema1:table1,table2', 'schema2:table1']);

        $this->assertCount(2, $output);

        $first = $output[0];

        $this->assertEquals('schema1', $first->getSchemaName());
        $this->assertEquals(['table1', 'table2'], $first->getTables());
        $this->assertEquals('/a/path/to/group/schema1', $first->getPath());
        $this->assertEquals($schema1, $first->getSchemaConfig());

        $second = $output[1];

        $this->assertEquals('schema2', $second->getSchemaName());
        $this->assertEquals(['table1'], $second->getTables());
        $this->assertEquals('/a/path/to/group/schema2', $second->getPath());
        $this->assertEquals($schema2, $second->getSchemaConfig());
    }

    public function testExtractSchemasWithSchemasOnlyHasEmptyTables()
    {
        $schema1 = Mockery::mock(SchemaConfigInterface::class);
        $schema2 = Mockery::mock(SchemaConfigInterface::class);
        $this->config->allows()
                     ->getSchemaConfiguration('schema1')
                     ->andReturns($schema1);
        $this->config->allows()
                     ->getSchemaPath($schema1, 'group')
                     ->andReturns('/a/path/to/group/schema1');
        $schema1->allows(['getSchema' => 'schema1']);

        $this->config->allows()
                     ->getSchemaConfiguration('schema2')
                     ->andReturns($schema2);
        $this->config->allows()
                     ->getSchemaPath($schema2, 'group')
                     ->andReturns('/a/path/to/group/schema2');
        $schema2->allows(['getSchema' => 'schema2']);

        $parsedSchemas = [];
        $this->tablePopulator->allows()
                             ->populateTables(Mockery::on(function (ParsedSchema $schema) use (&$parsedSchemas) {
                                 $parsedSchemas[] = $schema;
                                 return true;
                             }))
                             ->andReturnUsing(function () use (&$parsedSchemas) {
                                 return end($parsedSchemas);
                             });

        $output = $this->schemaParser->extractSchemas(['schema1', 'schema2']);

        $this->assertCount(2, $output);

        $first = $output[0];

        $this->assertEquals('schema1', $first->getSchemaName());
        $this->assertEquals([], $first->getTables());
        $this->assertEquals('/a/path/to/group/schema1', $first->getPath());
        $this->assertEquals($schema1, $first->getSchemaConfig());

        $second = $output[1];

        $this->assertEquals('schema2', $second->getSchemaName());
        $this->assertEquals([], $second->getTables());
        $this->assertEquals('/a/path/to/group/schema2', $second->getPath());
        $this->assertEquals($schema2, $second->getSchemaConfig());
    }

    public function testExtractSchemasWithMixedSchemasHasEmptyTables()
    {
        $schema1 = Mockery::mock(SchemaConfigInterface::class);
        $schema2 = Mockery::mock(SchemaConfigInterface::class);
        $this->config->allows()
                     ->getSchemaConfiguration('schema1')
                     ->andReturns($schema1);
        $this->config->allows()
                     ->getSchemaPath($schema1, 'group')
                     ->andReturns('/a/path/to/group/schema1');
        $schema1->allows(['getSchema' => 'schema1']);

        $this->config->allows()
                     ->getSchemaConfiguration('schema2')
                     ->andReturns($schema2);
        $this->config->allows()
                     ->getSchemaPath($schema2, 'group')
                     ->andReturns('/a/path/to/group/schema2');
        $schema2->allows(['getSchema' => 'schema2']);

        $parsedSchemas = [];
        $this->tablePopulator->allows()
                             ->populateTables(Mockery::on(function (ParsedSchema $schema) use (&$parsedSchemas) {
                                 $parsedSchemas[] = $schema;
                                 return true;
                             }))
                             ->andReturnUsing(function () use (&$parsedSchemas) {
                                 return end($parsedSchemas);
                             });

        $output = $this->schemaParser->extractSchemas(['schema1:table1', 'schema2']);

        $this->assertCount(2, $output);

        $first = $output[0];

        $this->assertEquals('schema1', $first->getSchemaName());
        $this->assertEquals(['table1'], $first->getTables());
        $this->assertEquals('/a/path/to/group/schema1', $first->getPath());
        $this->assertEquals($schema1, $first->getSchemaConfig());

        $second = $output[1];

        $this->assertEquals('schema2', $second->getSchemaName());
        $this->assertEquals([], $second->getTables());
        $this->assertEquals('/a/path/to/group/schema2', $second->getPath());
        $this->assertEquals($schema2, $second->getSchemaConfig());
    }

    public function testExtractSchemasWithNoSchemasLooksForAllSchemas()
    {
        $schema1 = Mockery::mock(SchemaConfigInterface::class);
        $schema2 = Mockery::mock(SchemaConfigInterface::class);

        $this->config->allows()
                     ->get('schemas')
                     ->andReturns([$schema1, $schema2]);

        $this->config->allows()
                     ->getSchemaConfiguration('schema1')
                     ->andReturns($schema1);
        $this->config->allows()
                     ->getSchemaPath($schema1, 'group')
                     ->andReturns('/a/path/to/group/schema1');
        $schema1->allows(['getSchema' => 'schema1']);

        $this->config->allows()
                     ->getSchemaConfiguration('schema2')
                     ->andReturns($schema2);
        $this->config->allows()
                     ->getSchemaPath($schema2, 'group')
                     ->andReturns('/a/path/to/group/schema2');
        $schema2->allows(['getSchema' => 'schema2']);

        $parsedSchemas = [];
        $this->tablePopulator->allows()
                             ->populateTables(Mockery::on(function (ParsedSchema $schema) use (&$parsedSchemas) {
                                 $parsedSchemas[] = $schema;
                                 return true;
                             }))
                             ->andReturnUsing(function () use (&$parsedSchemas) {
                                 return end($parsedSchemas);
                             });

        $output = $this->schemaParser->extractSchemas([]);

        $this->assertCount(2, $output);

        $first = $output[0];

        $this->assertEquals('schema1', $first->getSchemaName());
        $this->assertEquals([], $first->getTables());
        $this->assertEquals('/a/path/to/group/schema1', $first->getPath());
        $this->assertEquals($schema1, $first->getSchemaConfig());

        $second = $output[1];

        $this->assertEquals('schema2', $second->getSchemaName());
        $this->assertEquals([], $second->getTables());
        $this->assertEquals('/a/path/to/group/schema2', $second->getPath());
        $this->assertEquals($schema2, $second->getSchemaConfig());
    }
}
