<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_root_redirects_to_the_default_properties_page(): void
    {
        $this->get('/')->assertRedirect(route('site.properties', [
            'locale' => 'en',
            'city' => 'tbilisi',
            'type' => 'apartment',
        ]));
    }

    public function test_the_default_properties_page_returns_ok(): void
    {
        $this->get('/en/tbilisi/properties/apartment')->assertOk();
    }

    public function test_properties_path_rejects_unknown_type_segment(): void
    {
        $this->get('/en/tbilisi/properties/castle')->assertNotFound();
    }

    public function test_missing_listing_returns_not_found(): void
    {
        $this->get('/en/tbilisi/properties/999999999')->assertNotFound();
    }
}
