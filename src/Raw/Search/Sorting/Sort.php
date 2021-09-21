<?php

namespace Greensight\LaravelElasticQuery\Raw\Search\Sorting;

use Greensight\LaravelElasticQuery\Raw\Contracts\DSLAware;
use Greensight\LaravelElasticQuery\Raw\Contracts\SortMode;
use Greensight\LaravelElasticQuery\Raw\Contracts\SortOrder;
use Webmozart\Assert\Assert;

class Sort implements DSLAware
{
    public function __construct(
        private string $field,
        private string $order = SortOrder::ASC,
        private ?string $mode = null,
        private ?NestedSort $nested = null
    ) {
        Assert::stringNotEmpty(trim($field));
        Assert::oneOf($order, SortOrder::cases());
        Assert::nullOrOneOf($mode, SortMode::cases());
    }

    public function field(): string
    {
        return $this->field;
    }

    public function toDSL(): array
    {
        $details = [];

        if ($this->mode !== null) {
            $details['mode'] = $this->mode;
        }

        if ($this->nested !== null) {
            $details['nested'] = $this->nested->toDSL();
        }

        if ($this->order !== SortOrder::ASC) {
            $details['missing'] = '_first';
        }

        if (!$details) {
            return [$this->field => $this->order];
        }

        $details['order'] = $this->order;

        return [$this->field => $details];
    }

    public function __toString(): string
    {
        $order = $this->order === SortOrder::ASC ? '+' : '-';

        return "{$order}$this->field";
    }

    public function invert(): static
    {
        $order = $this->order === SortOrder::ASC ? SortOrder::DESC : SortOrder::ASC;

        return new static($this->field, $order, $this->mode, $this->nested);
    }
}
