<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Declarative;

use Greensight\LaravelElasticQuery\Declarative\CustomParameters;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\InvalidQueryException;
use Greensight\LaravelElasticQuery\Declarative\Filtering\AllowedFilter;
use Greensight\LaravelElasticQuery\Declarative\SearchQueryBuilder;
use Greensight\LaravelElasticQuery\Declarative\Sorting\AllowedSort;
use Greensight\LaravelElasticQuery\Declarative\Specification\CompositeSpecification;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Tests\Functional\SearchTestCase;

class SortingTest extends SearchTestCase
{
    private CompositeSpecification $spec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->spec = new CompositeSpecification();
    }

    public function testRootSort(): void
    {
        $this->spec->allowSorts('rating')
            ->where('tags', 'video');

        $this->build('-rating');

        $this->assertDocumentOrder([1, 328]);
    }

    public function testNestedSort(): void
    {
        $nested = Specification::new()
            ->allowSorts('+price')
            ->where('seller_id', 20);

        $this->spec->where('tags', 'video')
            ->addNested('offers', $nested);

        $this->build('-price');

        $this->assertDocumentOrder([328, 1]);
    }

    public function testComplexSort(): void
    {
        $this->spec->allowSorts(AllowedSort::field('-cashback', 'cashback.value'))
            ->allowFilters(AllowedFilter::exact('cashback', 'cashback.active'))
            ->addNested('offers', function (Specification $spec) {
                $spec->allowSorts('+price');
            });

        $this->build('cashback,-price', ['cashback' => 'true']);

        $this->assertDocumentOrder([328, 1, 150, 319]);
    }

    public function testValidateNames(): void
    {
        $this->expectException(InvalidQueryException::class);

        $this->spec->allowSorts('rating');

        $this->build(['unknown', 'rating']);
    }

    protected function build(array|string $sort, array $filter = []): void
    {
        $parameters = CustomParameters::make($filter, $sort);

        $builder = new SearchQueryBuilder($this->query, $this->spec, $parameters);

        $builder->validateResolved();
    }
}