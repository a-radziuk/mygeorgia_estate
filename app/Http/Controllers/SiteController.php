<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\SiteRepository;
use App\Models\Listing;
use App\Support\PropertyListingFilters;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function redirectRoot(): RedirectResponse
    {
        return redirect()->route('site.properties', [
            'locale' => SiteRepository::defaultLocale(),
            'city' => SiteRepository::defaultCity(),
            'type' => 'apartment',
        ]);
    }

    public function home(string $locale, string $city): View
    {
        if (! in_array($locale, SiteRepository::locales(), true)) {
            abort(404);
        }

        if (! in_array(strtolower($city), SiteRepository::cities(), true)) {
            abort(404);
        }

        $site = SiteRepository::forLocale($locale, $city);

        return view('home', [
            'locale' => $locale,
            'city' => strtolower($city),
            'page' => 'home',
            'site' => $site,
        ]);
    }

    public function listing(string $locale, string $city, Listing $listing): View
    {
        if (! in_array($locale, SiteRepository::locales(), true)) {
            abort(404);
        }

        $city = strtolower($city);
        if (! in_array($city, SiteRepository::cities(), true)) {
            abort(404);
        }

        if ($listing->locale !== $locale) {
            abort(404);
        }

        if ((string) $listing->city !== $city) {
            abort(404);
        }

        $site = SiteRepository::forLocale($locale, $city);
        $listingArr = $listing->toSiteArray();

        return view('listing', [
            'locale' => $locale,
            'city' => $city,
            'page' => 'listing',
            'site' => $site,
            'listing' => $listingArr,
            'propertiesTypeFilter' => in_array((string) $listing->type, SiteRepository::listingTypes(), true)
                ? $listing->type
                : null,
        ]);
    }

    public function properties(Request $request, string $locale, string $city, string $type): View
    {
        if (! in_array($locale, SiteRepository::locales(), true)) {
            abort(404);
        }

        $city = strtolower($city);
        if (! in_array($city, SiteRepository::cities(), true)) {
            abort(404);
        }

        $type = strtolower($type);
        if (! in_array($type, SiteRepository::listingTypes(), true)) {
            abort(404);
        }

        $filters = PropertyListingFilters::fromRequest($request);

        $site = SiteRepository::forLocale($locale, $city);
        $listingsQuery = Listing::query()
            ->where('locale', $locale)
            ->where('city', $city)
            ->where('type', $type)
            ->orderBy('korter_updated_at', 'desc')
        ;
        $filters->applyTo($listingsQuery);

        $listingsPaginator = $listingsQuery
            ->orderBy('listing_index')
            ->paginate(SiteRepository::LISTINGS_PER_PAGE)
            ->withQueryString()
            ->fragment('property-grid')
            ->through(fn (Listing $listing) => $listing->toSiteArray());

        return view('properties', [
            'locale' => $locale,
            'city' => $city,
            'page' => 'properties',
            'site' => $site,
            'listingsPaginator' => $listingsPaginator,
            'propertiesTypeFilter' => $type,
            'propertyFilters' => $filters,
        ]);
    }

    public function about(string $locale, string $city): View
    {
        return $this->render($locale, $city, 'about');
    }

    public function contact(string $locale, string $city): View
    {
        return $this->render($locale, $city, 'contact');
    }

    public function faqs(string $locale, string $city): View
    {
        return $this->render($locale, $city, 'faqs');
    }

    private function render(string $locale, string $city, string $page): View
    {
        if (! in_array($locale, SiteRepository::locales(), true)) {
            abort(404);
        }

        $city = strtolower($city);
        if (! in_array($city, SiteRepository::cities(), true)) {
            abort(404);
        }

        $site = SiteRepository::forLocale($locale, $city);

        return view($page, [
            'locale' => $locale,
            'city' => $city,
            'page' => $page,
            'site' => $site,
        ]);
    }
}
