<?php

namespace Graze\Sprout\Test\Unit\Parser;

use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Test\TestCase;
use Mockery;

class ParsedSchemaTest extends TestCase
{
    public function testConstructor()
    {
        $configuration = Mockery::mock(SchemaConfigInterface::class);
        $configuration->allows()
                      ->getSchema()
                      ->andReturns('the_schema');
        $parsedSchema = new ParsedSchema($configuration, 'path', ['table1', 'table2']);

        $this->assertSame($configuration, $parsedSchema->getSchemaConfig());
        $this->assertEquals('the_schema', $parsedSchema->getSchemaName());
        $this->assertEquals('path', $parsedSchema->getPath());
        $this->assertEquals(['table1', 'table2'], $parsedSchema->getTables());
    }

    public function testTheTablesCanBeChanged()
    {
        $configuration = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($configuration, 'path', []);

        $this->assertSame($configuration, $parsedSchema->getSchemaConfig());
        $this->assertEquals([], $parsedSchema->getTables());

        $this->assertSame($parsedSchema, $parsedSchema->setTables(['table1', 'table2', 'table3']));
        $this->assertEquals(['table1', 'table2', 'table3'], $parsedSchema->getTables());
    }
}
