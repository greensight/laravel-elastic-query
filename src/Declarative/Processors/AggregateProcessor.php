<?php

namespace Greensight\LaravelElasticQuery\Declarative\Processors;

use Greensight\LaravelElasticQuery\Declarative\Agregating\AllowedAggregate;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\InvalidQueryException;
use Greensight\LaravelElasticQuery\Declarative\Exceptions\NotUniqueNameException;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Declarative\Specification\Visitor;
use Greensight\LaravelElasticQuery\Raw\Contracts\AggregationsBuilder;
use Illuminate\Support\Collection;

class AggregateProcessor implements Visitor
{
    private Collection $requestedAggs;
    private Collection $allowedAggs;

    public function __construct(private AggregationsBuilder $builder, Collection $requestedAggs)
    {
        $this->requestedAggs = $requestedAggs->flip();
        $this->allowedAggs = new Collection();
    }

    public function visitRoot(Specification $specification): void
    {
        $this->getAllowedAggregates($specification)
            ->each(fn(AllowedAggregate $agg) => $agg($this->builder));
    }

    public function visitNested(string $field, Specification $specification): void
    {
        $this->getAllowedAggregates($specification)
            ->whenNotEmpty(
                fn(Collection $aggs) => $this->buildNested($field, $specification->constraints()->concat($aggs))
            );
    }

    public function done(): void
    {
        $diff = $this->requestedAggs->keys()->diff($this->allowedAggs->keys());

        if ($diff->count() > 0) {
            throw InvalidQueryException::notAllowedAggregates($diff);
        }
    }

    private function getAllowedAggregates(Specification $specification): Collection
    {
        return $specification->aggregates()
            ->each(fn(AllowedAggregate $agg) => $this->addAllowedAggregate($agg))
            ->intersectByKeys($this->requestedAggs);
    }

    private function buildNested(string $field, Collection $components): void
    {
        $this->builder->nested($field, function (AggregationsBuilder $builder) use ($components) {
            foreach ($components as $component) {
                $component($builder);
            }
        });
    }

    private function addAllowedAggregate(AllowedAggregate $agg): void
    {
        if ($this->allowedAggs->has($agg->name())) {
            throw NotUniqueNameException::aggregate($agg->name());
        }

        $this->allowedAggs[$agg->name()] = true;
    }
}