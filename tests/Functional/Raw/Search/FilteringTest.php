<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Raw\Search;

use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;
use Greensight\LaravelElasticQuery\Tests\Functional\SearchTestCase;

class FilteringTest extends SearchTestCase
{
    public function testWhere(): void
    {
        $this->query->where('code', 'tv');

        $this->assertDocumentIds([1]);
    }

    public function testWhereNot(): void
    {
        $this->query->whereNot('active', true);

        $this->assertDocumentIds([319]);
    }

    public function testWhereIn(): void
    {
        $this->query->whereIn('code', ['tv', 'water']);

        $this->assertDocumentIds([1, 150]);
    }

    public function testWhereNotIn(): void
    {
        $this->query->whereNotIn('tags', ['clothes', 'gloves', 'drinks', 'water']);

        $this->assertDocumentIds([1, 328]);
    }

    public function testWhereHas(): void
    {
        $this->query->whereHas('offers', function (BoolQuery $query) {
            $query->where('seller_id', 15)
                ->where('active', false);
        });

        $this->assertDocumentIds([319, 405]);
    }

    public function testWhereDoesntHave(): void
    {
        $this->query->whereDoesntHave('offers', function (BoolQuery $query) {
            $query->where('seller_id', 10)
                ->where('active', false);
        });

        $this->assertDocumentIds([1, 328, 471]);
    }

    public function testWhereNull(): void
    {
        $this->query->whereNull('cashback.active');

        $this->assertDocumentIds([405, 471]);
    }

    public function testWhereNotNull(): void
    {
        $this->query->whereNotNull('cashback');

        $this->assertDocumentIds([328, 1, 150, 319]);
    }
}
