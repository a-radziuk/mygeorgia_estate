@php
  $routeName = match ($page) {
      'home' => 'site.home',
      'properties' => 'site.properties',
      'about' => 'site.about',
      'contact' => 'site.contact',
      'faqs' => 'site.faqs',
      default => 'site.home',
  };
@endphp

<a class="skip-link" href="#main">{{ $site['skip'] }}</a>

<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="{{ route('site.home', ['locale' => $locale]) }}" aria-label="{{ $site['brand']['aria_home'] }}">
      <img src="{{ asset('assets/logo.svg') }}" alt="{{ $site['brand']['title'] }}"/>
      <div>
        {{ $site['brand']['title'] }}
        <small>{{ $site['brand']['subtitle'] }}</small>
      </div>
    </a>

    <nav class="nav" aria-label="{{ $site['nav_aria'] }}">
      <a href="{{ route('site.home', ['locale' => $locale]) }}" @if($page === 'home') aria-current="page" @endif>{{ $site['nav']['home'] }}</a>
      <a href="{{ route('site.properties', ['locale' => $locale]) }}" @if($page === 'properties') aria-current="page" @endif>{{ $site['nav']['properties'] }}</a>
      <a href="{{ route('site.about', ['locale' => $locale]) }}" @if($page === 'about') aria-current="page" @endif>{{ $site['nav']['about'] }}</a>
      <a href="{{ route('site.faqs', ['locale' => $locale]) }}" @if($page === 'faqs') aria-current="page" @endif>{{ $site['nav']['faq'] }}</a>
      <a href="{{ route('site.contact', ['locale' => $locale]) }}" @if($page === 'contact') aria-current="page" @endif>{{ $site['nav']['contact'] }}</a>
      <span class="muted" aria-hidden="true">|</span>
      <a href="{{ route($routeName, ['locale' => 'en']) }}" @if($locale === 'en') aria-current="page" @endif title="English">{{ $site['lang']['en'] }}</a>
      <a href="{{ route($routeName, ['locale' => 'ru']) }}" @if($locale === 'ru') aria-current="page" @endif title="Русский">{{ $site['lang']['ru'] }}</a>
      <a href="{{ route($routeName, ['locale' => 'ja']) }}" @if($locale === 'ja') aria-current="page" @endif title="日本語">{{ $site['lang']['ja'] }}</a>
    </nav>
  </div>
</header>
