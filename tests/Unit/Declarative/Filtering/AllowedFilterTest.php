<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Filtering;

use Greensight\LaravelElasticQuery\Declarative\Filtering\AllowedFilter;
use Greensight\LaravelElasticQuery\Declarative\Contracts\FilterAction;
use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;
use Mockery\MockInterface;

class AllowedFilterTest extends UnitTestCase
{
    private FilterAction|MockInterface $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = Mockery::mock(FilterAction::class);
    }

    /**
     * @dataProvider provideConstructSetsField
     */
    public function testConstructSetsField(string $name, ?string $field, string $expected): void
    {
        $this->expectsApplyFilter(Mockery::any(), $expected);

        $this->invokeAllowedFilter($name, $field, 1);
    }

    public function provideConstructSetsField(): array
    {
        return [
            'only name' => ['foo', null, 'foo'],
            'name and field' => ['foo', 'bar', 'bar'],
        ];
    }

    public function testApply(): void
    {
        $this->expectsApplyFilter(1, Mockery::any());

        $this->invokeAllowedFilter('foo', null, 1);
    }

    public function testApplyNotCalledWhenNotActive(): void
    {
        $this->filter->expects('apply')->never();

        $this->invokeAllowedFilter('foo');
    }

    /**
     * @dataProvider provideSetValue
     */
    public function testSetValue(mixed $value, mixed $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->createAllowedFilter('foo')->setValue($value)->value()
        );
    }

    public function provideSetValue(): array
    {
        return [
            'string' => ['foo', 'foo'],
            'boolean' => [false, false],
            'filled array' => [[1, null], [1, null]],
            'empty array' => [[], null],
            'array of only nulls' => [[null, null], null],
        ];
    }

    /**
     * @dataProvider provideIsActive
     */
    public function testIsActive(bool $enabled, mixed $value, bool $expected): void
    {
        $testing = $this->createAllowedFilter()->setValue($value);
        $enabled ? $testing->enable() : $testing->disable();

        $this->assertEquals($expected, $testing->isActive());
    }

    public function provideIsActive(): array
    {
        return [
            'enabled and has value' => [true, 100, true],
            'enabled and no value' => [true, null, false],
            'disabled and has value' => [false, 100, false],
            'disabled and no value' => [false, null, false],
        ];
    }

    /**
     * @dataProvider provideValue
     */
    public function testValue(mixed $value, mixed $default, mixed $expected): void
    {
        $testing = $this->createAllowedFilter()
            ->default($default)
            ->setValue($value);

        $this->assertEquals($expected, $testing->value());
    }

    public function provideValue(): array
    {
        return [
            'only value' => [35, null, 35],
            'only default' => [null, 100, 100],
            'both value and default' => [35, 100, 35],
            'nothing' => [null, null, null],
        ];
    }

    private function createAllowedFilter(string $name = 'name', ?string $field = null): AllowedFilter
    {
        return new AllowedFilter($name, $this->filter, $field);
    }

    private function invokeAllowedFilter(string $name = 'name', ?string $field = null, mixed $value = null): void
    {
        $allowedFilter = $this->createAllowedFilter($name, $field);

        if ($value !== null) {
            $allowedFilter->setValue($value);
        }

        $allowedFilter(Mockery::mock(BoolQuery::class));
    }

    private function expectsApplyFilter(mixed $value, mixed $field): void
    {
        $this->filter->expects('__invoke')
            ->with(Mockery::any(), $value, $field)
            ->once();
    }
}