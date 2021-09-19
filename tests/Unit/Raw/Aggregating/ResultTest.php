<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Raw\Aggregating;

use Greensight\LaravelElasticQuery\Raw\Aggregating\Bucket;
use Greensight\LaravelElasticQuery\Raw\Aggregating\Result;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class ResultTest extends UnitTestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(array $source, mixed $expected): void
    {
        $this->assertEquals($expected, Result::parse($source, 'value'));
    }

    public function provideParse(): array
    {
        return [
            'only value' => [['value' => 10.5], 10.5],
            'has string value' => [['value' => 1, 'value_as_string' => 'foo'], 'foo'],
            'true as string' => [['value' => 1, 'value_as_string' => 'true'], true],
            'false as string' => [['value' => 1, 'value_as_string' => 'false'], false],
            'datetime' => [['value' => 25, 'value_as_string' => '2021-08-29T15:19:34.000Z'], '2021-08-29T15:19:34.000Z'],
        ];
    }

    /**
     * @dataProvider provideParseBucket
     */
    public function testParseBucket(array $source, Bucket $expected): void
    {
        $this->assertEquals($expected, Result::parseBucket($source));
    }

    public function provideParseBucket(): array
    {
        return [
            'single key' => [['key' => 20, 'doc_count' => 1], new Bucket(20, 1)],
            'complex key' => [['key' => 20, 'key_as_string' => 'true', 'doc_count' => 1], new Bucket(true, 1)],
            'without doc_count' => [['key' => 20], new Bucket(20, 0)],
        ];
    }
}
