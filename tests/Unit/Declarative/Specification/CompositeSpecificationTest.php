<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Declarative\Specification;

use Greensight\LaravelElasticQuery\Declarative\Specification\CompositeSpecification;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Declarative\Specification\Visitor;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class CompositeSpecificationTest extends UnitTestCase implements Visitor
{
    private CompositeSpecification $testing;

    private ?Specification $rootSpecification = null;
    private array $nestedSpecifications = [];
    private array $nestedFields = [];
    private bool $done = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rootSpecification = null;
        $this->nestedSpecifications = [];
        $this->nestedFields = [];
        $this->done = false;

        $this->testing = new CompositeSpecification();
    }

    public function testForwardCallReturnsSelf(): void
    {
        $this->assertSame(
            $this->testing,
            $this->testing->allowFilters('foo')
        );
    }

    public function testAddNested(): void
    {
        $this->testing->addNested('foo', fn() => null);

        $this->testing->accept($this);

        $this->assertEquals(['foo'], $this->nestedFields);
    }

    public function testAddNestedInstance(): void
    {
        $expected = Specification::new();
        $this->testing->addNested('foo', $expected);

        $this->testing->accept($this);

        $this->assertSame($expected, $this->nestedSpecifications[0]);
    }

    public function testAcceptVisitsRoot(): void
    {
        $this->testing->accept($this);

        $this->assertNotNull($this->rootSpecification);
    }

    public function testAcceptVisitsNested(): void
    {
        $this->testing->addNested('foo', fn() => null);

        $this->testing->accept($this);

        $this->assertCount(1, $this->nestedSpecifications);
    }

    public function testAcceptDone(): void
    {
        $this->testing->accept($this);

        $this->assertTrue($this->done);
    }

    //region Visitor implementation
    public function visitRoot(Specification $specification): void
    {
        $this->assertNull($this->rootSpecification);

        $this->rootSpecification = $specification;
    }

    public function visitNested(string $field, Specification $specification): void
    {
        $this->nestedFields[] = $field;
        $this->nestedSpecifications[] = $specification;
    }

    public function done(): void
    {
        $this->done = true;
    }
    //endregion
}
