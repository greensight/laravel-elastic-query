<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Concerns;

use Greensight\LaravelElasticQuery\Declarative\Concerns\ExtractsQueryParameters;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class ExtractsQueryParametersTest extends UnitTestCase
{
    /**
     * @dataProvider provideConvertFilterValues
     */
    public function testConvertFilterValues(mixed $value, mixed $expected): void
    {
        $parameters = new ExtractsQueryParametersStub([
            'filter' => ['name' => $value],
        ]);

        $this->assertEquals(['name' => $expected], $parameters->filters()->all());
    }

    public function provideConvertFilterValues(): array
    {
        return [
            'true' => ['true', true],
            'false' => ['false', false],
            'array of boolean' => [['true', 'false'], [true, false]],
            'assoc array' => [['foo' => 'bar', 'baz' => 'true'], ['foo' => 'bar', 'baz' => true]],
        ];
    }

    /**
     * @dataProvider provideSorts
     */
    public function testSorts(mixed $value, array $expected): void
    {
        $parameters = new ExtractsQueryParametersStub(['sort' => $value]);

        $this->assertEquals($expected, $parameters->sorts()->all());
    }

    public function provideSorts(): array
    {
        return [
            'array' => [['foo', '-bar'], ['foo', '-bar']],
            'string' => ['-foo,+bar, baz', ['-foo', '+bar', 'baz']],
            'with empty' => ['foo,,bar', ['foo', 'bar']],
        ];
    }
}

class ExtractsQueryParametersStub
{
    use ExtractsQueryParameters;

    public function __construct(public array $source)
    {
    }

    protected function extract(string $key): mixed
    {
        return $this->source[$key] ?? null;
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return $default ?? $key;
    }
}