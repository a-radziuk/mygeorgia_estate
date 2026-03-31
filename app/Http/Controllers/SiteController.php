<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\SiteRepository;
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
        return $this->render($locale, 'home');
    }

    public function properties(string $locale): View
    {
        return $this->render($locale, 'properties');
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
