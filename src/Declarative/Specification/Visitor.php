<?php

namespace Greensight\LaravelElasticQuery\Declarative\Specification;

use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;

interface Visitor
{
    public function visitRoot(Specification $specification): void;

    public function visitNested(string $field, Specification $specification): void;

    public function done(): void;
}