<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Processors;

use Greensight\LaravelElasticQuery\Declarative\Exceptions\ComponentExistsException;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\InvalidQueryException;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\NotUniqueNameException;
use Greensight\LaravelElasticQuery\Declarative\Processors\SortProcessor;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Raw\Contracts\SortableQuery;
use Greensight\LaravelElasticQuery\Raw\Contracts\SortOrder;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;

class SortProcessorTest extends UnitTestCase
{
    private Specification $specification;
    private SortableQuery|Mockery\MockInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specification = new Specification();
        $this->query = Mockery::mock(SortableQuery::class);
    }

    public function testRootSorts(): void
    {
        $this->specification->allowSorts('foo', 'bar', 'baz');

        $this->query->expects('sortBy')
            ->andReturnSelf()
            ->times(3);

        $this->execute(['foo', '+bar', '-baz']);
    }

    public function testNestedSorts(): void
    {
        $this->specification->allowSorts('foo');

        $this->query->expects('sortByNested')
            ->with('nested', Mockery::any())
            ->andReturnSelf()
            ->once();

        $this->execute(['-foo'], 'nested');
    }

    public function testNotAllowedSorts(): void
    {
        $this->specification->allowSorts('foo');

        $this->expectException(InvalidQueryException::class);
        $this->query->expects('sortBy')->andReturnSelf()->never();

        $this->execute(['+bar']);
    }

    public function testDuplicatedSortNames(): void
    {
        $this->specification->allowSorts('foo');

        $this->expectException(NotUniqueNameException::class);

        $processor = new SortProcessor($this->query, collect(['foo']));
        $processor->visitRoot($this->specification);
        $processor->visitNested('nested', $this->specification);
    }

    public function testPassOrder(): void
    {
        $this->specification->allowSorts('foo', 'bar', 'baz');

        $this->query->expects('sortBy')
            ->with('foo', SortOrder::DESC, null)
            ->andReturnSelf()
            ->once();

        $this->execute(['-foo']);
    }

    private function execute(array $sorts, ?string $nested = null): void
    {
        $processor = new SortProcessor($this->query, collect($sorts));

        $nested === null
            ? $processor->visitRoot($this->specification)
            : $processor->visitNested($nested, $this->specification);

        $processor->done();
    }
}