<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Processors;

use Greensight\LaravelElasticQuery\Declarative\Processors\FilterProcessor;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\InvalidQueryException;
use Greensight\LaravelElasticQuery\Declarative\Filtering\AllowedFilter;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use function collect;

class FilterProcessorTest extends UnitTestCase
{
    private Specification $specification;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specification = new Specification();
    }

    public function testDone(): void
    {
        $this->specification->allowFilters('foo', 'bar');

        $this->createAndVisit()->done();

        $this->expectNotToPerformAssertions();
    }

    public function testExistsNotAllowed(): void
    {
        $this->expectException(InvalidQueryException::class);

        $this->specification->allowFilters('foo');

        $this->createAndVisit(['foo' => 1, 'baz' => 2])->done();
    }

    public function testSetValues(): void
    {
        $filter = AllowedFilter::exact('foo');
        $this->specification->allowFilters($filter);

        $this->createAndVisit(['foo' => 1]);

        $this->assertSame(1, $filter->value());
    }

    public function testIgnoreMissingValues(): void
    {
        $filter = AllowedFilter::exact('foo');
        $this->specification->allowFilters($filter);

        $this->createAndVisit();

        $this->assertNull($filter->value());
    }

    private function createAndVisit(array $values = []): FilterProcessor
    {
        $filler = new FilterProcessor(collect($values));
        $filler->visitRoot($this->specification);

        return $filler;
    }
}