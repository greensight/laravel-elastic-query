<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional;

use Greensight\LaravelElasticQuery\Raw\Search\SearchQuery;
use Greensight\LaravelElasticQuery\Tests\Functional\ElasticTestCase;
use Greensight\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Greensight\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;

class SearchTestCase extends ElasticTestCase
{
    const TOTAL_PRODUCTS = 6;

    protected SearchQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        ProductIndexSeeder::run();
        $this->query = $this->makeSearchQuery(ProductsIndex::class);
    }

    protected function assertDocumentIds(array $expected): void
    {
        $actual = $this->query->get()
            ->pluck('_id')
            ->all();

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function assertDocumentOrder(array $expected): void
    {
        $actual = $this->query->get()
            ->pluck('_id')
            ->all();

        $this->assertEquals($expected, $actual);
    }
}
