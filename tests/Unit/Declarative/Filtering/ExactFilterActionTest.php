<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Filtering;

use Greensight\LaravelElasticQuery\Declarative\Filtering\ExactFilterAction;
use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;
use Mockery\MockInterface;

class ExactFilterActionTest extends UnitTestCase
{
    private ExactFilterAction $testing;
    private BoolQuery|MockInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = Mockery::mock(BoolQuery::class);
        $this->testing = new ExactFilterAction();
    }

    /**
     * @dataProvider provideSingleValue
     */
    public function testSingleValue(mixed $value, mixed $expected): void
    {
        $this->query->expects('where')
            ->with('single', $expected)
            ->once()
            ->andReturnSelf();

        $this->apply('single', $value);
    }

    public function provideSingleValue(): array
    {
        return [
            'scalar' => ['foo', 'foo'],
            'single value array' => [[null, 'foo'], 'foo'],
        ];
    }

    /**
     * @dataProvider provideMultiValue
     */
    public function testMultiValue(mixed $value, mixed $expected): void
    {
        $this->query->expects('whereIn')
            ->with('multi', $expected)
            ->once()
            ->andReturnSelf();

        $this->apply('multi', $value);
    }

    public function provideMultiValue(): array
    {
        return [
            'only values' => [[1, 2], [1, 2]],
            'mixed with nulls' => [[null, 1, null, 2], [1, 2]],
        ];
    }

    private function apply(string $field, mixed $value): void
    {
        $this->testing->__invoke($this->query, $value, $field);
    }
}