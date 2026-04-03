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
        $this->get('/')->assertRedirect(route('site.properties', ['locale' => 'en', 'city' => 'tbilisi']));
    }

    public function test_the_default_properties_page_returns_ok(): void
    {
        $this->get('/en/tbilisi/properties')->assertOk();
    }

    public function test_missing_listing_returns_not_found(): void
    {
        $this->get('/en/tbilisi/properties/999999999')->assertNotFound();
    }
}
