@extends('layouts.site')

@section('title', $site['meta']['properties']['title'])
@section('description', $site['meta']['properties']['description'])

@section('content')
  @php $p = $site['pages']['properties']; @endphp
  <div id="top" aria-hidden="true"></div>

  <section class="hero" style="margin-bottom: 1.4rem;">
    <div class="hero-card">
      <div class="eyebrow"><span class="dot" aria-hidden="true"></span> {{ $p['hero_eyebrow'] }}</div>
      <h1>{{ $p['hero_title'] }}</h1>
      <p>{{ $p['hero_text'] }}</p>
      <div class="actions">
        <a class="btn btn-primary" href="#property-grid">{{ $p['cta_grid'] }}</a>
        <a class="btn btn-ghost" href="{{ route('site.contact', ['locale' => $locale, 'city' => $city]) }}#contact-form">{{ $p['cta_viewing'] }}</a>
      </div>
    </div>

    <aside class="hero-aside">
      <b>{{ $p['aside_title'] }}</b>
      <p class="muted" style="margin: .6rem 0 0;">{{ $p['aside_text'] }}</p>
      <div style="margin-top: 1rem;">
        <div class="field">
          <label for="search">{{ $p['search_label'] }}</label>
          <input id="search" name="search" placeholder="{{ $p['search_placeholder'] }}" disabled/>
        </div>
        <div class="field">
          <label for="budget">{{ $p['budget_label'] }}</label>
          <select id="budget" name="budget" disabled>
            <option>{{ $p['budget_option'] }}</option>
          </select>
        </div>
      </div>
      <div class="notice" style="margin-top: 1rem;">{!! $p['aside_notice'] !!}</div>
    </aside>
  </section>

  <section class="section" aria-labelledby="property-grid">
    <div class="section-title">
      <h2 id="property-grid">{{ $p['section_title'] }}</h2>
      <p>
        @if ($listingsPaginator->total() > 0)
          {{ str_replace([':from', ':to', ':total'], [$listingsPaginator->firstItem(), $listingsPaginator->lastItem(), $listingsPaginator->total()], $p['pagination_summary']) }}
        @else
          {{ $p['section_subtitle'] }}
        @endif
      </p>
    </div>

    @if ($listingsPaginator->count() === 0)
      <p class="muted">{{ $p['empty_listings'] }}</p>
    @else
    <div class="grid grid-3" role="list" aria-label="{{ $p['grid_aria'] }}">
      @foreach ($listingsPaginator as $listing)
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
            <a class="btn btn-primary" href="{{ route('site.listing', array_filter([
              'locale' => $locale,
              'city' => $city,
              'listing' => $listing['id'],
              'return_page' => request()->integer('page', 1) > 1 ? request('page') : null,
            ], fn ($v) => $v !== null && $v !== '')) }}">{{ $p['view_details'] }}</a>
          </div>
        </article>
      @endforeach
    </div>

    {{ $listingsPaginator->links('vendor.pagination.listings', ['p' => $p]) }}
    @endif
  </section>
@endsection
