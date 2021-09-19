<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Raw\Aggregating;

use Greensight\LaravelElasticQuery\Raw\Contracts\AggregationsBuilder;
use Greensight\LaravelElasticQuery\Tests\Functional\AggregationTestCase;

class AggregationQueryTest extends AggregationTestCase
{
    public function testGet(): void
    {
        $this->query
            ->where('package', 'bottle')
            ->terms('codes', 'code')
            ->nested(
                'offers',
                fn (AggregationsBuilder $builder) => $builder->where('seller_id', 10)->minmax('price', 'price')
            );

        $this->assertBucketKeys('codes', ['voda-san-pellegrino-mineralnaya-gazirovannaya', 'water']);
        $this->assertMinMax('price', 168.0, 611.0);
    }

    public function testComposite(): void
    {
        $this->query->composite(function (AggregationsBuilder $builder) {
            $builder->where('package', 'bottle')
                ->terms('codes', 'code');
        });

        $this->assertBucketKeys('codes', ['voda-san-pellegrino-mineralnaya-gazirovannaya', 'water']);
    }
}
