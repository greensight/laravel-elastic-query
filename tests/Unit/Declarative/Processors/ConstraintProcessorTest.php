<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Processors;

use Greensight\LaravelElasticQuery\Declarative\Processors\ConstraintProcessor;
use Greensight\LaravelElasticQuery\Declarative\Filtering\AllowedFilter;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;

class ConstraintProcessorTest extends UnitTestCase
{
    private Specification $specification;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specification = new Specification();
    }

    public function testVisitRoot(): void
    {
        $this->specification->where('foo', 10);

        $query = Mockery::mock(BoolQuery::class);
        $query->expects('where')
            ->with('foo', 10)
            ->once()
            ->andReturnSelf();

        (new ConstraintProcessor($query))->visitRoot($this->specification);
    }

    public function testVisitNested(): void
    {
        $this->specification->allowFilters(AllowedFilter::exact('foo')->default(10));

        $query = Mockery::mock(BoolQuery::class);
        $query->expects('whereHas')
            ->with('nested', Mockery::any())
            ->once()
            ->andReturnSelf();

        (new ConstraintProcessor($query))->visitNested('nested', $this->specification);
    }

    public function testVisitNestedNoActiveFilters(): void
    {
        $this->specification->where('foo', 10);

        $query = Mockery::mock(BoolQuery::class);
        $query->expects('whereHas')->never();

        (new ConstraintProcessor($query))->visitNested('nested', $this->specification);
    }
}