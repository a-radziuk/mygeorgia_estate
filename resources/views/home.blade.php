@extends('layouts.site')

@section('title', $site['meta']['home']['title'])
@section('description', $site['meta']['home']['description'])

@section('content')
  @php $p = $site['pages']['home']; @endphp

  <section class="hero">
    <div class="hero-card">
      <div class="eyebrow"><span class="dot" aria-hidden="true"></span> {{ $p['hero_eyebrow'] }}</div>
      <h1>{{ $p['hero_title'] }}</h1>
      <p>{{ $p['hero_text'] }}</p>
      <div class="actions">
        <a class="btn btn-primary" href="{{ route('site.properties', ['locale' => $locale, 'city' => $city, 'type' => 'apartment']) }}#top">{{ $p['cta_browse'] }}</a>
        <a class="btn btn-ghost" href="{{ route('site.contact', ['locale' => $locale, 'city' => $city]) }}#contact-form">{{ $p['cta_contact'] }}</a>
      </div>
    </div>

    <aside class="hero-aside" aria-label="Company highlights">
      <b>{{ $p['aside_title'] }}</b>
      <div class="stat-grid" role="list">
        @foreach ($p['stats'] as $row)
          <div class="stat" role="listitem">
            <b>{{ $row['b'] }}</b>
            <span>{{ $row['label'] }}</span>
          </div>
        @endforeach
      </div>
      <p class="muted" style="margin: .9rem 0 0;">{{ $p['stat_note'] }}</p>
    </aside>
  </section>

  <section class="section">
    <div class="section-title">
      <h2>{{ $p['featured_title'] }}</h2>
      <p>{{ $p['featured_subtitle'] }}</p>
    </div>

    <div class="grid grid-3" role="list" aria-label="{{ $p['featured_title'] }}">
      @foreach ($site['featured_ids'] as $id)
        @continue(! isset($site['listings'][$id]))
        @php $listing = $site['listings'][$id]; @endphp
        <article class="card" role="listitem">
          <div class="property-media">
            <img src="{{ \App\Support\ListingMedia::url($listing['image']) }}" alt="{{ $listing['image_alt'] }}"/>
          </div>
          <div class="card-body">
            <span class="kicker">{{ $listing['kicker'] }}</span>
            <h3>{{ $listing['title'] }}</h3>
            <div class="price">{{ $listing['price'] }}</div>
            @if (!empty($listing['price_per_sqm']) || !empty($listing['district']))
              <div class="listing-card-sub muted">
                @if (!empty($listing['price_per_sqm'])){{ $listing['price_per_sqm'] }}@endif
                @if (!empty($listing['price_per_sqm']) && !empty($listing['district'])) · @endif
                @if (!empty($listing['district'])){{ $listing['district'] }}@endif
              </div>
            @endif
            <div class="meta-row">
              @foreach ($listing['chips'] as $chip)
                <span class="chip">{{ $chip }}</span>
              @endforeach
            </div>
          </div>
          <div class="card-actions">
            <a class="btn btn-primary" href="{{ route('site.listing', ['locale' => $locale, 'city' => $city, 'listing' => $listing['id']]) }}">{{ $site['pages']['properties']['view_details'] }}</a>
          </div>
        </article>
      @endforeach
    </div>
  </section>
@endsection
