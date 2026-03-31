<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\SiteRepository;
use App\Models\Listing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SiteController extends Controller
{
    public function redirectRoot(): RedirectResponse
    {
        return redirect()->route('site.properties', ['locale' => SiteRepository::defaultLocale()]);
    }

    public function home(string $locale): View
    {
        if (! in_array($locale, SiteRepository::locales(), true)) {
            abort(404);
        }

        $site = SiteRepository::forLocale($locale);
        $featuredListingPages = [];
        foreach ($site['featured_ids'] as $id) {
            $featuredListingPages[(int) $id] = SiteRepository::listingPageForIndex($locale, (int) $id);
        }

        return view('home', [
            'locale' => $locale,
            'page' => 'home',
            'site' => $site,
            'featuredListingPages' => $featuredListingPages,
        ]);
    }

    public function properties(string $locale): View
    {
        if (! in_array($locale, SiteRepository::locales(), true)) {
            abort(404);
        }

        $site = SiteRepository::forLocale($locale);
        $listingsPaginator = Listing::query()
            ->where('locale', $locale)
            ->orderBy('listing_index')
            ->paginate(SiteRepository::LISTINGS_PER_PAGE)
            ->withQueryString()
            ->fragment('property-grid')
            ->through(fn (Listing $listing) => $listing->toSiteArray());

        return view('properties', [
            'locale' => $locale,
            'page' => 'properties',
            'site' => $site,
            'listingsPaginator' => $listingsPaginator,
        ]);
    }

    public function about(string $locale): View
    {
        return $this->render($locale, 'about');
    }

    public function contact(string $locale): View
    {
        return $this->render($locale, 'contact');
    }

    public function faqs(string $locale): View
    {
        return $this->render($locale, 'faqs');
    }

    private function render(string $locale, string $page): View
    {
        if (! in_array($locale, SiteRepository::locales(), true)) {
            abort(404);
        }

        $site = SiteRepository::forLocale($locale);

        return view($page, [
            'locale' => $locale,
            'page' => $page,
            'site' => $site,
        ]);
    }
}
