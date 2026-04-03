@extends('layouts.site')

@section('title', $site['meta']['about']['title'])
@section('description', $site['meta']['about']['description'])

@section('content')
  @php $p = $site['pages']['about']; @endphp

  <section class="hero" style="margin-bottom: 1.4rem;">
    <div class="hero-card">
      <div class="eyebrow"><span class="dot" aria-hidden="true"></span> {{ $p['hero_eyebrow'] }}</div>
      <h1>{{ $p['hero_title'] }}</h1>
      <p>{{ $p['hero_text'] }}</p>
      <div class="actions">
        <a class="btn btn-primary" href="{{ route('site.properties', ['locale' => $locale, 'city' => $city, 'type' => 'apartment']) }}#top">{{ $p['cta_listings'] }}</a>
        <a class="btn btn-ghost" href="{{ route('site.contact', ['locale' => $locale, 'city' => $city]) }}#contact-form">{{ $p['cta_contact'] }}</a>
      </div>
    </div>

    <aside class="hero-aside" aria-label="Company stats">
      <b>{{ $p['aside_title'] }}</b>
      <div class="stat-grid" role="list" style="margin-top: .9rem;">
        @foreach ($p['stats'] as $row)
          <div class="stat" role="listitem">
            <b>{{ $row['b'] }}</b>
            <span>{{ $row['label'] }}</span>
          </div>
        @endforeach
      </div>
      <div class="notice" style="margin-top: 1rem;">{{ $p['aside_notice'] }}</div>
    </aside>
  </section>

  <section class="section">
    <div class="section-title">
      <h2>{{ $p['mission_title'] }}</h2>
      <p>{{ $p['mission_subtitle'] }}</p>
    </div>

    <div class="grid grid-3">
      @foreach ($p['cards'] as $card)
        <div class="card"><div class="card-body">
          <b>{{ $card['title'] }}</b>
          <p class="muted" style="margin: .45rem 0 0;">{{ $card['text'] }}</p>
        </div></div>
      @endforeach
    </div>
  </section>
@endsection
