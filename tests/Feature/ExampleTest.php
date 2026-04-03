<?php

namespace Tests\Feature;

use App\Models\Listing;
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

    public function test_property_listing_query_filters_narrow_results(): void
    {
        $base = [
            'is_mock' => true,
            'locale' => 'en',
            'city' => 'tbilisi',
            'type' => 'apartment',
            'listing_index' => 9101,
            'code' => 'FX-1',
            'images' => [['file' => 'property-1.svg', 'alt' => 'x']],
            'image_alt' => 'x',
            'kicker' => 'FX-1',
            'title' => 'Filtered test A',
            'price' => '200,000 GEL',
            'price_amount' => 200000,
            'price_currency' => 'GEL',
            'price_per_sqm' => '2,000 GEL/m²',
            'price_per_sqm_amount' => 2000,
            'market_type' => 'primary',
            'room_count' => 3,
            'total_area_sqm' => 100,
            'chips' => ['100 m²', '3 rooms'],
            'modal_anchor' => 'fx1',
            'modal_title' => 'FX-1 modal',
            'address' => 'Test address',
            'bullets' => [],
            'tip' => 'tip',
        ];
        Listing::query()->create($base);
        Listing::query()->create(array_merge($base, [
            'listing_index' => 9102,
            'code' => 'FX-2',
            'title' => 'Filtered test B',
            'market_type' => 'secondary',
            'price_amount' => 500000,
            'price_per_sqm_amount' => 5000,
            'room_count' => 5,
            'modal_anchor' => 'fx2',
        ]));

        $url = route('site.properties', ['locale' => 'en', 'city' => 'tbilisi', 'type' => 'apartment']);
        $this->get($url.'?'.http_build_query([
            'market_type' => 'primary',
            'price_max' => '300000',
            'rooms_max' => '4',
        ]))->assertOk()
            ->assertSee('Filtered test A', false)
            ->assertDontSee('Filtered test B', false);
    }
}
