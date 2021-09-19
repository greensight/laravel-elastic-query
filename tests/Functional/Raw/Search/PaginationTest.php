<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Raw\Search;

use Greensight\LaravelElasticQuery\Raw\Search\Cursor;
use Greensight\LaravelElasticQuery\Tests\Functional\SearchTestCase;

class PaginationTest extends SearchTestCase
{
    public function testCursorPaginate(): void
    {
        $this->query->sortBy('package')
            ->sortBy('rating', 'desc');

        $page = $this->query->cursorPaginate(2);

        $this->assertEquals(Cursor::BOF()->encode(), $page->current);
        $this->assertNull($page->previous);

        $pageNext = $this->query->cursorPaginate(2, $page->next);

        $this->assertEquals($page->current, $pageNext->previous);
        $this->assertTrue(true);
    }

    public function testPage(): void
    {
        $page = $this->query->sortBy('product_id')->paginate(2, 1);

        $this->assertEquals(self::TOTAL_PRODUCTS, $page->total);
        $this->assertEquals(1, $page->offset);
        $this->assertEquals(2, $page->size);
        $this->assertCount(2, $page->hits);
        $this->assertEquals(150, $page->hits[0]['_source']['product_id']);
    }
}
