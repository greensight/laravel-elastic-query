<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional;

use Greensight\LaravelElasticQuery\Raw\Aggregating\AggregationsQuery;
use Greensight\LaravelElasticQuery\Raw\Aggregating\MinMax;
use Greensight\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Greensight\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;
use Illuminate\Support\Collection;

class AggregationTestCase extends ElasticTestCase
{
    protected AggregationsQuery $query;

    protected ?Collection $results = null;

    protected function setUp(): void
    {
        parent::setUp();

        ProductIndexSeeder::run();

        $this->results = null;
        $this->query = $this->makeAggregationsQuery(ProductsIndex::class);
    }

    protected function results(string $aggName): mixed
    {
        $this->results ??= $this->query->get();

        return $this->results->get($aggName);
    }

    protected function assertBucketKeys(string $aggName, array $expected): void
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $this->results($aggName)->pluck('key')->all()
        );
    }

    protected function assertMinMax(string $aggName, mixed $min, mixed $max): void
    {
        $this->assertEquals(new MinMax($min, $max), $this->results($aggName));
    }
}