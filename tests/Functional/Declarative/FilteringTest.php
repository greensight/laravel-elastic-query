<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Declarative;

use Greensight\LaravelElasticQuery\Declarative\CustomParameters;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\InvalidQueryException;
use Greensight\LaravelElasticQuery\Declarative\Filtering\AllowedFilter;
use Greensight\LaravelElasticQuery\Declarative\SearchQueryBuilder;
use Greensight\LaravelElasticQuery\Declarative\Specification\CompositeSpecification;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Tests\Functional\SearchTestCase;

class FilteringTest extends SearchTestCase
{
    private CompositeSpecification $spec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->spec = new CompositeSpecification();
    }

    public function testRoot(): void
    {
        $this->spec->allowFilters('code')
            ->where('active', true);

        $this->build('code', ['tv', 'gloves']);

        $this->assertDocumentIds([1]);
    }

    public function testNested(): void
    {
        $this->spec->addNested(
            'offers',
            Specification::new()
                ->allowFilters('active')
                ->where('seller_id', 20)
        );
        $this->spec->addNested(
            'offers',
            Specification::new()
                ->allowFilters('active')
                ->where('seller_id', 90)
        );

        $this->build('active', true);

        $this->assertDocumentIds([328]);
    }

    public function testValidateNames(): void
    {
        $this->expectException(InvalidQueryException::class);

        $this->spec->allowFilters('code', 'active');

        $this->build(['code' => 'tv', 'unknown' => 10]);
    }

    /**
     * @dataProvider provideExistsFilter
     */
    public function testExistsFilter(bool $value, int $expectedCount): void
    {
        $this->spec->allowFilters(AllowedFilter::exists('cashback', 'cashback.active'));

        $this->build('cashback', $value);

        $this->assertCount($expectedCount, $this->query->get());
    }

    public function provideExistsFilter(): array
    {
        return [
            'true' => [true, 4],
            'false' => [false, 2],
        ];
    }

    protected function build(array|string $filter, mixed $value = null): void
    {
        $parameters = CustomParameters::make(filter: is_array($filter) ? $filter : [$filter => $value]);

        $builder = new SearchQueryBuilder($this->query, $this->spec, $parameters);

        $builder->validateResolved();
    }
}