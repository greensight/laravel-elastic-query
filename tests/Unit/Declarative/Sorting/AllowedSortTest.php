<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Sorting;

use Greensight\LaravelElasticQuery\Declarative\Sorting\AllowedSort;
use Greensight\LaravelElasticQuery\Declarative\Contracts\SortAction;
use Greensight\LaravelElasticQuery\Raw\Contracts\SortableQuery;
use Greensight\LaravelElasticQuery\Raw\Contracts\SortMode;
use Greensight\LaravelElasticQuery\Raw\Contracts\SortOrder;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;
use Mockery\MockInterface;

class AllowedSortTest extends UnitTestCase
{
    private SortAction|MockInterface $strategy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->strategy = Mockery::mock(SortAction::class);
    }

    /**
     * @dataProvider provideNameAndOrder
     */
    public function testParseNameAndOrder(string $source, string $expectedName, ?string $expectedOrder): void
    {
        [$name, $order] = AllowedSort::parseNameAndOrder($source);

        $this->assertSame($name, $expectedName, 'Invalid name');
        $this->assertSame($order, $expectedOrder, 'Invalid order');
    }

    public function provideNameAndOrder(): array
    {
        return [
            'without order' => ['foo', 'foo', null],
            'ascending' => ['+foo', 'foo', SortOrder::ASC],
            'descending' => ['-foo', 'foo', SortOrder::DESC],
        ];
    }

    public function testConstructSetsDefaultOrder(): void
    {
        $this->expectsApplyStrategy(SortOrder::DESC, Mockery::any(), 'foo');

        $this->invokeAllowedSort('-foo');
    }

    /**
     * @dataProvider provideConstructSetsField
     */
    public function testConstructSetsField(string $name, ?string $field, string $expected): void
    {
        $this->expectsApplyStrategy(Mockery::any(), Mockery::any(), $expected);

        $this->invokeAllowedSort($name, $field);
    }

    public function provideConstructSetsField(): array
    {
        return [
            'only name' => ['foo', null, 'foo'],
            'name and field' => ['foo', 'bar', 'bar'],
        ];
    }

    public function testMode(): void
    {
        $this->expectsApplyStrategy(Mockery::any(), SortMode::MEDIAN, Mockery::any());

        $this->invokeAllowedSort('-foo', mode: SortMode::MEDIAN);
    }

    private function createAllowedSort(string $name = 'name', ?string $field = null): AllowedSort
    {
        return new AllowedSort($name, $this->strategy, $field);
    }

    private function invokeAllowedSort(string $name, ?string $field = null, ?string $mode = null, ?string $order = null): void
    {
        $allowedSort = $this->createAllowedSort($name, $field);

        if ($mode !== null) {
            $allowedSort->mode($mode);
        }

        $allowedSort(Mockery::mock(SortableQuery::class), $order);
    }

    private function expectsApplyStrategy(mixed $order, mixed $mode, mixed $field): void
    {
        $this->strategy->expects('__invoke')
            ->with(Mockery::any(), $order, $mode, $field)
            ->once();
    }
}