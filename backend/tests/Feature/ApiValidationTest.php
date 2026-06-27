<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    public function test_product_index_rejects_invalid_price_and_pagination_parameters(): void
    {
        $this->getJson('/api/products?min_price=-1')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('min_price');

        $this->getJson('/api/products?min_price=500000&max_price=100000')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('max_price');

        $this->getJson('/api/products?page=0&per_page=25')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page', 'per_page']);
    }

    public function test_product_index_rejects_oversized_search_and_filter_lists(): void
    {
        $this->getJson('/api/products?search='.str_repeat('a', 101))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('search');

        $categories = implode(',', array_map(
            fn (int $index): string => "category-{$index}",
            range(1, 21),
        ));

        $this->getJson('/api/products?category='.$categories)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');
    }

    public function test_recent_product_inputs_are_bounded_and_well_formed(): void
    {
        $slugs = implode(',', array_map(
            fn (int $index): string => "product-{$index}",
            range(1, 13),
        ));

        $this->getJson('/api/products/recent?slugs='.$slugs)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slugs');

        $ids = implode(',', range(1, 51));

        $this->getJson('/api/home?recent_product_ids='.$ids)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('recent_product_ids');

        $this->getJson('/api/home?recent_product_ids=1,abc,3')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('recent_product_ids');
    }

    public function test_blog_index_rejects_invalid_query_parameters(): void
    {
        $this->getJson('/api/blogs?sort=oldest&page=0&per_page=25')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort', 'page', 'per_page']);

        $this->getJson('/api/blogs?search='.str_repeat('a', 101))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('search');
    }
}
