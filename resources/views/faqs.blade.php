@extends('layouts.site')

@section('title', $site['meta']['faqs']['title'])
@section('description', $site['meta']['faqs']['description'])

@section('content')
  @php $p = $site['pages']['faqs']; @endphp

  <section class="hero" style="margin-bottom: 1.4rem;">
    <div class="hero-card">
      <div class="eyebrow"><span class="dot" aria-hidden="true"></span> {{ $p['hero_eyebrow'] }}</div>
      <h1>{{ $p['hero_title'] }}</h1>
      <p>{!! $p['hero_text'] !!}</p>
      <div class="actions">
        <a class="btn btn-primary" href="{{ route('site.properties', ['locale' => $locale]) }}#top">{{ $p['cta_browse'] }}</a>
        <a class="btn btn-ghost" href="{{ route('site.contact', ['locale' => $locale]) }}#contact-form">{{ $p['cta_ask'] }}</a>
      </div>
    </div>

    <aside class="hero-aside">
      <b>{{ $p['aside_title'] }}</b>
      <p class="muted" style="margin: .6rem 0 0;">{{ $p['aside_text'] }}</p>
      <div class="notice" style="margin-top: 1rem;">{{ $p['aside_notice'] }}</div>
    </aside>
  </section>

  <section class="section">
    <div class="section-title">
      <h2>{{ $p['section_title'] }}</h2>
      <p>{{ $p['section_subtitle'] }}</p>
    </div>

    @foreach ($site['faqs'] as $faq)
      <details class="faq">
        <summary>{{ $faq['q'] }}</summary>
        <p>{{ $faq['a'] }}</p>
      </details>
    @endforeach
  </section>
@endsection
