<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_redirects_to_the_default_properties_page(): void
    {
        $this->get('/')->assertRedirect(route('site.properties', ['locale' => 'en']));
    }

    public function test_the_default_properties_page_returns_ok(): void
    {
        $this->get('/en/properties')->assertOk();
    }
}
