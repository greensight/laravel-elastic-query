<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Specification;

use Greensight\LaravelElasticQuery\Declarative\Agregating\AllowedAggregate;
use Greensight\LaravelElasticQuery\Declarative\Contracts\Constraint;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\ComponentExistsException;
use Greensight\LaravelElasticQuery\Declarative\Filtering\AllowedFilter;
use Greensight\LaravelElasticQuery\Declarative\Specification\CallbackConstraint;
use Greensight\LaravelElasticQuery\Declarative\Sorting\AllowedSort;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;

class SpecificationTest extends UnitTestCase
{
    public function testAllowFilters(): void
    {
        $spec = Specification::new()
            ->allowFilters('foo', AllowedFilter::exact('bar'));

        $this->assertCount(2, $spec->filters());
    }

    public function testAddConstraint(): void
    {
        $spec = Specification::new()
            ->addConstraint(Mockery::mock(Constraint::class));

        $this->assertCount(1, $spec->constraints());
    }

    public function testAddCallbackConstraint(): void
    {
        $spec = Specification::new()->addConstraint(fn() => null);

        $this->assertContainsOnlyInstancesOf(CallbackConstraint::class, $spec->constraints());
    }

    public function testConstraintsIncludesFilters(): void
    {
        $spec = Specification::new()
            ->allowFilters('foo')
            ->addConstraint(fn() => null);

        $this->assertCount(2, $spec->constraints());
    }

    public function testAllowSorts(): void
    {
        $spec = Specification::new()
            ->allowSorts('foo', AllowedSort::field('bar'));

        $this->assertCount(2, $spec->sorts());
    }

    public function testAllowAggregates(): void
    {
        $spec = Specification::new()
            ->allowAggregates('foo', AllowedAggregate::terms('bar'));

        $this->assertCount(2, $spec->aggregates());
    }

    /**
     * @dataProvider provideDuplicateComponentName
     */
    public function testDuplicateComponentName(string $method): void
    {
        $this->expectException(ComponentExistsException::class);

        Specification::new()->{$method}('foo', 'bar', 'foo');
    }

    public function provideDuplicateComponentName(): array
    {
        return [
            'filter' => ['allowFilters'],
            'sort' => ['allowSorts'],
            'aggregate' => ['allowAggregates'],
        ];
    }
}
