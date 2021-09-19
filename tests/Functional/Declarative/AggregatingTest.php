<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Declarative;

use Greensight\LaravelElasticQuery\Declarative\AggregateQueryBuilder;
use Greensight\LaravelElasticQuery\Declarative\Agregating\AllowedAggregate;
use Greensight\LaravelElasticQuery\Declarative\CustomParameters;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\InvalidQueryException;
use Greensight\LaravelElasticQuery\Declarative\Specification\CompositeSpecification;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Tests\Functional\AggregationTestCase;

class AggregatingTest extends AggregationTestCase
{
    private CompositeSpecification $spec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->spec = new CompositeSpecification();
    }

    public function testRoot(): void
    {
        $this->spec->allowAggregates('tags');

        $this->build('tags');

        $this->assertBucketKeys('tags', ['water', 'video', 'gloves', 'clothes', 'drinks']);
    }

    public function testNested(): void
    {
        $this->spec->addNested('offers', function (Specification $spec) {
            $spec->allowFilters('active')
                ->allowAggregates(AllowedAggregate::minmax('price'))
                ->where('seller_id', 10);
        });

        $this->build('price', ['active' => false]);

        $this->assertMinMax('price', 168.0, 980.0);
    }

    public function testMultipleNested(): void
    {
        $this->spec->addNested('offers', function (Specification $spec) {
            $spec->allowAggregates(AllowedAggregate::minmax('price_active', 'price'))
                ->where('seller_id', 10)
                ->where('active', true);
        });

        $this->spec->addNested('offers', function (Specification $spec) {
            $spec->allowAggregates(AllowedAggregate::minmax('price_inactive', 'price'))
                ->where('seller_id', 10)
                ->where('active', false);
        });

        $this->build('price_active,price_inactive');

        $this->assertMinMax('price_inactive', 168.0, 980.0);
        $this->assertMinMax('price_active', 20000.0, 20000.0);
    }

    public function testNestedMultipleAggregates(): void
    {
        $this->spec->addNested('offers', function (Specification $spec) {
            $spec->allowAggregates(
                'seller_id',
                AllowedAggregate::minmax('price'),
            );
        });

        $this->spec->addNested('offers', function (Specification $spec) {
            $spec->allowFilters('active');
        });

        $this->spec->where('tags', 'drinks');

        $this->build(['seller_id', 'price'], ['active' => true]);

        $this->assertBucketKeys('seller_id', [10, 15, 20]);
        $this->assertMinMax('price', 168.0, 210.0);
    }

    public function testValidateNames(): void
    {
        $this->expectException(InvalidQueryException::class);

        $this->spec->allowAggregates('tags');

        $this->build(['tags', 'unknown']);
    }

    private function build(array|string $aggs, array $filter = []): void
    {
        $parameters = CustomParameters::make(filter: $filter, aggs: $aggs);

        $builder = new AggregateQueryBuilder($this->query, $this->spec, $parameters);

        $builder->validateResolved();
    }
}