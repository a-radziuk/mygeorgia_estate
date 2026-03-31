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
        <a class="btn btn-ghost" href="{{ route('site.contact', ['locale' => $locale]) }}#contact-form">{{ $p['cta_viewing'] }}</a>
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
      <p>{{ $p['section_subtitle'] }}</p>
    </div>

    <div class="grid grid-3" role="list" aria-label="{{ $p['grid_aria'] }}">
      @foreach ($site['listings'] as $listing)
        <article class="card" role="listitem">
          <div class="property-media">
            <img src="{{ asset('assets/'.$listing['image']) }}" alt="{{ $listing['image_alt'] }}"/>
          </div>
          <div class="card-body">
            <span class="kicker">{{ $listing['kicker'] }}</span>
            <h3>{{ $listing['title'] }}</h3>
            <div class="price">{{ $listing['price'] }}</div>
            <div class="meta-row">
              @foreach ($listing['chips'] as $chip)
                <span class="chip">{{ $chip }}</span>
              @endforeach
            </div>
          </div>
          <div class="card-actions">
            <a class="btn btn-primary" href="#{{ $listing['modal_anchor'] }}">{{ $p['view_details'] }}</a>
          </div>
        </article>
      @endforeach
    </div>
  </section>

  @foreach ($site['listings'] as $listing)
    <section class="property-detail" id="{{ $listing['modal_anchor'] }}" aria-label="{{ $listing['code'] }} details">
      <div class="property-detail-modal" role="dialog" aria-modal="true" aria-labelledby="{{ $listing['modal_anchor'] }}-title">
        <div class="modal-inner">
          <div class="modal-top">
            <h3 id="{{ $listing['modal_anchor'] }}-title">{{ $listing['modal_title'] }}</h3>
            <a class="close-link" href="{{ route('site.properties', ['locale' => $locale]) }}#top" aria-label="{{ $p['close_label'] }}">{{ $p['close_label'] }}</a>
          </div>
          <div class="modal-body">
            <div class="modal-media">
              <img src="{{ asset('assets/'.$listing['image']) }}" alt="{{ $listing['image_alt'] }}"/>
            </div>
            <div class="modal-content">
              <div class="price">{{ $listing['price'] }}</div>
              <p class="muted" style="margin: .4rem 0 .9rem;">{{ $listing['address'] }}</p>
              <ul class="bullets">
                @foreach ($listing['bullets'] as $b)
                  <li><b>{{ $b['label'] }}</b> {{ $b['text'] }}</li>
                @endforeach
              </ul>
              <div class="modal-footer-actions">
                <a class="btn btn-primary" href="{{ route('site.contact', ['locale' => $locale]) }}#contact-form">{{ $p['request_viewing'] }}</a>
                <a class="btn btn-ghost" href="{{ route('site.properties', ['locale' => $locale]) }}#top">{{ $p['back_listings'] }}</a>
              </div>
              <p class="muted" style="margin: .85rem 0 0;">{!! $listing['tip'] !!}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endforeach
@endsection
