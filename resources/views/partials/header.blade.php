@php
  $routeName = match ($page) {
      'home' => 'site.home',
      'properties' => 'site.properties',
      'listing' => 'site.properties',
      'about' => 'site.about',
      'contact' => 'site.contact',
      'faqs' => 'site.faqs',
      default => 'site.home',
  };
  $cityRouteParams = ['locale' => $locale, 'city' => $city];
  $typeForSwitcher = $propertiesTypeFilter ?? null;
  if ($typeForSwitcher === null && ($page ?? '') === 'listing' && isset($listing) && is_array($listing) && in_array((string) ($listing['type'] ?? ''), ['apartment', 'house'], true)) {
      $typeForSwitcher = $listing['type'];
  }
  $switchBase = array_merge(
      $cityRouteParams,
      ($routeName === 'site.properties' && $typeForSwitcher) ? ['type' => $typeForSwitcher] : []
  );
@endphp

<a class="skip-link" href="#main">{{ $site['skip'] }}</a>

<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="{{ route('site.home', $cityRouteParams) }}" aria-label="{{ $site['brand']['aria_home'] }}">
      <img src="{{ asset('assets/logo.svg') }}" alt="{{ $site['brand']['title'] }}"/>
      <div>
        {{ $site['brand']['title'] }}
        <small>{{ $site['brand']['subtitle'] }}</small>
      </div>
    </a>

    <nav class="nav" aria-label="{{ $site['nav_aria'] }}">
      <a href="{{ route('site.home', $cityRouteParams) }}" @if($page === 'home') aria-current="page" @endif>{{ $site['nav']['home'] }}</a>
      <a href="{{ route('site.properties', array_merge($cityRouteParams, ['type' => 'apartment'])) }}" @if(($page === 'properties' && ($propertiesTypeFilter ?? null) === 'apartment') || ($page === 'listing' && isset($listing) && ($listing['type'] ?? '') === 'apartment')) aria-current="page" @endif>{{ $site['nav']['apartments'] }}</a>
      <a href="{{ route('site.properties', array_merge($cityRouteParams, ['type' => 'house'])) }}" @if(($page === 'properties' && ($propertiesTypeFilter ?? null) === 'house') || ($page === 'listing' && isset($listing) && ($listing['type'] ?? '') === 'house')) aria-current="page" @endif>{{ $site['nav']['houses'] }}</a>
      <a href="{{ route('site.about', $cityRouteParams) }}" @if($page === 'about') aria-current="page" @endif>{{ $site['nav']['about'] }}</a>
      <a href="{{ route('site.faqs', $cityRouteParams) }}" @if($page === 'faqs') aria-current="page" @endif>{{ $site['nav']['faq'] }}</a>
      <a href="{{ route('site.contact', $cityRouteParams) }}" @if($page === 'contact') aria-current="page" @endif>{{ $site['nav']['contact'] }}</a>
      <span class="muted nav-divider" aria-hidden="true">|</span>
      <details class="nav-dropdown nav-city-dropdown" aria-label="{{ $site['city_menu_aria'] }}">
        <summary class="nav-dropdown-trigger">
          <span class="nav-dropdown-value">{{ $site['cities'][$city] }}</span>
          <span class="nav-dropdown-chevron" aria-hidden="true"></span>
        </summary>
        <ul class="nav-dropdown-panel">
          <li>
            <a href="{{ route($routeName, array_merge($switchBase, ['city' => 'tbilisi'])) }}" @if($city === 'tbilisi') aria-current="page" @endif>{{ $site['cities']['tbilisi'] }}</a>
          </li>
          <li>
            <a href="{{ route($routeName, array_merge($switchBase, ['city' => 'batumi'])) }}" @if($city === 'batumi') aria-current="page" @endif>{{ $site['cities']['batumi'] }}</a>
          </li>
        </ul>
      </details>
      <span class="muted nav-divider" aria-hidden="true">|</span>
      <a href="{{ route($routeName, array_merge($switchBase, ['locale' => 'en'])) }}" @if($locale === 'en') aria-current="page" @endif title="English">{{ $site['lang']['en'] }}</a>
      <a href="{{ route($routeName, array_merge($switchBase, ['locale' => 'ru'])) }}" @if($locale === 'ru') aria-current="page" @endif title="Русский">{{ $site['lang']['ru'] }}</a>
      <a href="{{ route($routeName, array_merge($switchBase, ['locale' => 'ja'])) }}" @if($locale === 'ja') aria-current="page" @endif title="日本語">{{ $site['lang']['ja'] }}</a>
    </nav>
  </div>
</header>
