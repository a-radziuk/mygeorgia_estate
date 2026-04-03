@extends('layouts.site')

@section('title', $site['meta']['properties']['title'])
@section('description', $site['meta']['properties']['description'])

@section('content')
  @php
    $p = $site['pages']['properties'];
    $f = $propertyFilters;
    $moreFiltersOpen = $f->pricePerSqmMin !== null || $f->pricePerSqmMax !== null
        || $f->roomsMin !== null || $f->roomsMax !== null
        || $f->areaMin !== null || $f->areaMax !== null;
  @endphp
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
      <form class="filters-form" method="get" action="{{ route('site.properties', ['locale' => $locale, 'city' => $city, 'type' => $propertiesTypeFilter]) }}" style="margin-top: 1rem;">
        <div class="field">
          <label for="filter-market">{{ $p['filter_market_label'] }}</label>
          <select id="filter-market" name="market_type">
            <option value="" @selected($f->marketType === null)>{{ $p['filter_market_any'] }}</option>
            <option value="primary" @selected($f->marketType === 'primary')>{{ $p['filter_market_primary'] }}</option>
            <option value="secondary" @selected($f->marketType === 'secondary')>{{ $p['filter_market_secondary'] }}</option>
          </select>
        </div>
        <div class="field">
          <span class="filters-fieldset-label">{{ $p['filter_price_label'] }}</span>
          <div class="filters-row">
            <select id="filter-price-min" name="price_min" aria-label="{{ $p['filter_price_min_aria'] }}">
              <option value="" @selected($f->priceMin === null)>{{ $p['filter_market_any'] }}</option>
              @foreach ($p['filter_price_suggest_total'] as $opt)
              <option value="{{ $opt['v'] }}" @selected($f->priceMin !== null && abs($f->priceMin - (float) $opt['v']) < 0.01)>{{ $opt['label'] }}</option>
              @endforeach
            </select>
            <span class="filters-sep muted" aria-hidden="true">–</span>
            <select id="filter-price-max" name="price_max" aria-label="{{ $p['filter_price_max_aria'] }}">
              <option value="" @selected($f->priceMax === null)>{{ $p['filter_market_any'] }}</option>
              @foreach ($p['filter_price_suggest_total'] as $opt)
              <option value="{{ $opt['v'] }}" @selected($f->priceMax !== null && abs($f->priceMax - (float) $opt['v']) < 0.01)>{{ $opt['label'] }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <details class="filters-more" @if ($moreFiltersOpen) open @endif>
          <summary class="filters-more-summary">{{ $p['filter_more_toggle'] }}</summary>
          <div class="filters-more-body">
            <div class="field">
              <span class="filters-fieldset-label">{{ $p['filter_price_sqm_label'] }}</span>
              <div class="filters-row">
                <select id="filter-price-sqm-min" name="price_sqm_min" aria-label="{{ $p['filter_price_sqm_min_aria'] }}">
                  <option value="" @selected($f->pricePerSqmMin === null)>{{ $p['filter_market_any'] }}</option>
                  @foreach ($p['filter_price_suggest_sqm'] as $opt)
                  <option value="{{ $opt['v'] }}" @selected($f->pricePerSqmMin !== null && abs($f->pricePerSqmMin - (float) $opt['v']) < 0.01)>{{ $opt['label'] }}</option>
                  @endforeach
                </select>
                <span class="filters-sep muted" aria-hidden="true">–</span>
                <select id="filter-price-sqm-max" name="price_sqm_max" aria-label="{{ $p['filter_price_sqm_max_aria'] }}">
                  <option value="" @selected($f->pricePerSqmMax === null)>{{ $p['filter_market_any'] }}</option>
                  @foreach ($p['filter_price_suggest_sqm'] as $opt)
                  <option value="{{ $opt['v'] }}" @selected($f->pricePerSqmMax !== null && abs($f->pricePerSqmMax - (float) $opt['v']) < 0.01)>{{ $opt['label'] }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="field">
              <span class="filters-fieldset-label">{{ $p['filter_rooms_label'] }}</span>
              <div class="filters-row">
                <input type="number" name="rooms_min" min="1" step="1" placeholder="{{ $p['filter_min'] }}" value="{{ $f->roomsMin !== null ? (string) $f->roomsMin : '' }}" aria-label="{{ $p['filter_rooms_min_aria'] }}"/>
                <span class="filters-sep muted" aria-hidden="true">–</span>
                <input type="number" name="rooms_max" min="1" step="1" placeholder="{{ $p['filter_max'] }}" value="{{ $f->roomsMax !== null ? (string) $f->roomsMax : '' }}" aria-label="{{ $p['filter_rooms_max_aria'] }}"/>
              </div>
            </div>
            <div class="field">
              <span class="filters-fieldset-label">{{ $p['filter_area_label'] }}</span>
              <div class="filters-row">
                <input type="number" name="area_min" min="0" step="any" placeholder="{{ $p['filter_min'] }}" value="{{ $f->areaMin !== null ? (string) $f->areaMin : '' }}" aria-label="{{ $p['filter_area_min_aria'] }}"/>
                <span class="filters-sep muted" aria-hidden="true">–</span>
                <input type="number" name="area_max" min="0" step="any" placeholder="{{ $p['filter_max'] }}" value="{{ $f->areaMax !== null ? (string) $f->areaMax : '' }}" aria-label="{{ $p['filter_area_max_aria'] }}"/>
              </div>
              <small class="muted" style="display:block;margin-top:.35rem;">{{ $p['filter_area_suffix'] }}</small>
            </div>
          </div>
        </details>
        <div class="filters-actions">
          <button type="submit" class="btn btn-primary">{{ $p['filter_submit'] }}</button>
          <a class="btn btn-ghost" href="{{ route('site.properties', ['locale' => $locale, 'city' => $city, 'type' => $propertiesTypeFilter]) }}">{{ $p['filter_clear'] }}</a>
        </div>
      </form>
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
      <p class="muted">{{ $f->isActive() ? $p['empty_listings_filtered'] : $p['empty_listings'] }}</p>
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
