<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Processors;

use Greensight\LaravelElasticQuery\Declarative\Exceptions\InvalidQueryException;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\NotUniqueNameException;
use Greensight\LaravelElasticQuery\Declarative\Processors\AggregateProcessor;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Raw\Contracts\AggregationsBuilder;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;

class AggregateProcessorTest extends UnitTestCase
{
    private Specification $specification;
    private AggregationsBuilder|Mockery\MockInterface $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specification = new Specification();
        $this->builder = Mockery::mock(AggregationsBuilder::class);
    }

    public function testRootAggregates(): void
    {
        $this->specification->allowAggregates('foo', 'bar');

        $this->expectsOnce('terms', 'foo', 'foo');

        $this->execute(['foo']);
    }

    public function testNestedAggregates(): void
    {
        $this->specification->allowAggregates('foo', 'bar');

        $this->expectsOnce('nested', 'field', Mockery::any());

        $this->execute(['foo'], 'field');
    }

    public function testEmptyNested(): void
    {
        $this->specification->allowAggregates('foo');

        $this->builder->expects('nested')->never();

        $this->execute([], 'field');
    }

    public function testNestedConstraints(): void
    {
        $this->specification->allowAggregates('foo')
            ->where('bar', 10);

        $this->expectsOnce('terms', 'foo', 'foo');
        $this->expectsOnce('where', 'bar', 10);

        $this->builder->allows('nested')
            ->with('field', Mockery::any())
            ->andReturnUsing(function ($field, callable $callback) {
                $callback($this->builder);
                return $this->builder;
            });

        $this->execute(['foo'], 'field');
    }

    public function testNotAllowedAggregates(): void
    {
        $this->specification->allowAggregates('foo');

        $this->expectException(InvalidQueryException::class);

        $this->execute(['bar']);
    }

    public function testDuplicateAggregateNames(): void
    {
        $this->specification->allowAggregates('foo');
        $this->builder->allows('terms')->andReturnSelf();

        $this->expectException(NotUniqueNameException::class);

        $processor = new AggregateProcessor($this->builder, collect(['foo']));
        $processor->visitRoot($this->specification);
        $processor->visitNested('nested', $this->specification);
    }

    private function execute(array $aggs, ?string $nested = null): void
    {
        $processor = new AggregateProcessor($this->builder, collect($aggs));

        $nested === null
            ? $processor->visitRoot($this->specification)
            : $processor->visitNested($nested, $this->specification);

        $processor->done();
    }

    private function expectsOnce(string $method, mixed ...$arguments): void
    {
        $this->builder->expects($method)
            ->with(...$arguments)
            ->once()
            ->andReturnSelf();
    }
}