@extends('layouts.site')

@section('title', $site['meta']['properties']['title'])
@section('description', $site['meta']['properties']['description'])

@section('content')
  @php
    $p = $site['pages']['properties'];
    $propertiesIndexHref = route('site.properties', array_filter([
      'locale' => $locale,
      'page' => request()->integer('page', 1) > 1 ? request()->integer('page') : null,
    ], fn ($v) => $v !== null && $v !== ''));
  @endphp
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
            <a class="btn btn-primary" href="#{{ $listing['modal_anchor'] }}">{{ $p['view_details'] }}</a>
          </div>
        </article>
      @endforeach
    </div>

    {{ $listingsPaginator->links('vendor.pagination.listings', ['p' => $p]) }}
    @endif
  </section>

  @foreach ($listingsPaginator as $listing)
    <section class="property-detail" id="{{ $listing['modal_anchor'] }}" aria-label="{{ $listing['code'] }} details">
      <div class="property-detail-modal" role="dialog" aria-modal="true" aria-labelledby="{{ $listing['modal_anchor'] }}-title">
        <div class="modal-inner">
          <div class="modal-top">
            <h3 id="{{ $listing['modal_anchor'] }}-title">{{ $listing['modal_title'] }}</h3>
            <a class="close-link" href="{{ $propertiesIndexHref }}#top" aria-label="{{ $p['close_label'] }}">{{ $p['close_label'] }}</a>
          </div>
          <div class="modal-body">
            <div class="modal-media">
              @php
                $slides = array_map(fn (array $img) => [
                  'src' => \App\Support\ListingMedia::url($img['file']),
                  'alt' => $img['alt'] ?? '',
                ], $listing['images'] ?? []);
              @endphp
              @if (count($slides) > 0)
                <div
                  class="modal-gallery"
                  data-images="{{ e(json_encode($slides)) }}"
                  data-counter-template="{{ $p['gallery_counter'] }}"
                  tabindex="-1"
                >
                  <div class="modal-gallery-viewport">
                    <img
                      class="modal-gallery-img"
                      src="{{ $slides[0]['src'] }}"
                      alt="{{ $slides[0]['alt'] }}"
                      loading="lazy"
                    />
                    @if (count($slides) > 1)
                      <button type="button" class="modal-gallery-nav modal-gallery-prev" aria-label="{{ $p['gallery_prev'] }}">‹</button>
                      <button type="button" class="modal-gallery-nav modal-gallery-next" aria-label="{{ $p['gallery_next'] }}">›</button>
                    @endif
                  </div>
                  @if (count($slides) > 1)
                    <div class="modal-gallery-toolbar">
                      <span class="modal-gallery-counter muted"></span>
                      <div class="modal-gallery-dots" role="group" aria-label="{{ $listing['code'] }}">
                        @foreach ($slides as $i => $slide)
                          <button
                            type="button"
                            class="modal-gallery-dot{{ $i === 0 ? ' is-active' : '' }}"
                            data-index="{{ $i }}"
                            aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                            aria-label="{{ $slide['alt'] }} ({{ $i + 1 }}/{{ count($slides) }})"
                          ></button>
                        @endforeach
                      </div>
                    </div>
                  @endif
                </div>
              @endif
              @if (!empty($listing['description_by_developer']))
                <div class="listing-developer-description">
                  <div class="listing-developer-heading muted">{{ $p['label_description_by_developer'] }}</div>
                  <p>{{ $listing['description_by_developer'] }}</p>
                </div>
              @endif
            </div>
            <div class="modal-content">
              <div class="price">{{ $listing['price'] }}</div>

              <dl class="listing-specs">
                @if (!empty($listing['price_per_sqm']))
                  <div class="listing-specs-row">
                    <dt>{{ $p['label_price_per_sqm'] }}</dt>
                    <dd>{{ $listing['price_per_sqm'] }}</dd>
                  </div>
                @endif
                @if (!empty($listing['address_line']))
                  <div class="listing-specs-row">
                    <dt>{{ $p['label_address'] }}</dt>
                    <dd>{{ $listing['address_line'] }}</dd>
                  </div>
                @endif
                @if (!empty($listing['district']))
                  <div class="listing-specs-row">
                    <dt>{{ $p['label_district'] }}</dt>
                    <dd>{{ $listing['district'] }}</dd>
                  </div>
                @endif
                @if (!empty($listing['developer']))
                  <div class="listing-specs-row">
                    <dt>{{ $p['label_developer'] }}</dt>
                    <dd>{{ $listing['developer'] }}</dd>
                  </div>
                @endif
                @if (!empty($listing['built_year']))
                  <div class="listing-specs-row">
                    <dt>{{ $p['label_built_year'] }}</dt>
                    <dd>{{ $listing['built_year'] }}</dd>
                  </div>
                @endif
              </dl>

              @if (!empty($listing['latitude']) && !empty($listing['longitude']))
                <div class="listing-map">
                  <div class="listing-map-label muted">{{ $p['label_map'] }}</div>
                  <div class="listing-map-frame">
                    <iframe
                      title="{{ $p['label_map'] }} — {{ $listing['code'] }}"
                      loading="lazy"
                      referrerpolicy="no-referrer-when-downgrade"
                      src="https://www.google.com/maps?q={{ $listing['latitude'] }},{{ $listing['longitude'] }}&z=15&output=embed"
                    ></iframe>
                  </div>
                  <a
                    class="listing-map-external"
                    href="https://www.google.com/maps/search/?api=1&amp;query={{ $listing['latitude'] }},{{ $listing['longitude'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                  >{{ $p['map_open_external'] }}</a>
                </div>
              @endif

              <p class="muted listing-notes">{{ $listing['address'] }}</p>
              <ul class="bullets">
                @foreach ($listing['bullets'] as $b)
                  <li><b>{{ $b['label'] }}</b> {{ $b['text'] }}</li>
                @endforeach
              </ul>
              <div class="modal-footer-actions">
                <a class="btn btn-primary" href="{{ route('site.contact', ['locale' => $locale]) }}#contact-form">{{ $p['request_viewing'] }}</a>
                <a class="btn btn-ghost" href="{{ $propertiesIndexHref }}#top">{{ $p['back_listings'] }}</a>
              </div>
              <p class="muted" style="margin: .85rem 0 0;">{!! $listing['tip'] !!}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endforeach
@endsection

@push('scripts')
  <script>
    (function () {
      const galleries = new Map();


      function parseImages(root) {
        try {
          return JSON.parse(root.dataset.images.replace(/&quot;/gi, '"') || '[]');
        } catch (e) {
          return [];
        }
      }

      function initGallery(root) {
        const data = parseImages(root);
        if (!data.length) return;

        const img = root.querySelector('.modal-gallery-img');
        const prev = root.querySelector('.modal-gallery-prev');
        const next = root.querySelector('.modal-gallery-next');
        const counter = root.querySelector('.modal-gallery-counter');
        const dots = root.querySelectorAll('.modal-gallery-dot');
        const tpl = root.dataset.counterTemplate || 'Image :current of :total';

        let idx = 0;

        function show(i) {
          idx = (i + data.length) % data.length;
          const s = data[idx];
          img.src = s.src;
          img.alt = s.alt;
          if (counter) {
            counter.textContent = tpl
              .replace(':current', String(idx + 1))
              .replace(':total', String(data.length));
          }
          dots.forEach(function (d, di) {
            d.classList.toggle('is-active', di === idx);
            d.setAttribute('aria-current', di === idx ? 'true' : 'false');
          });
        }

        prev?.addEventListener('click', function () { show(idx - 1); });
        next?.addEventListener('click', function () { show(idx + 1); });
        dots.forEach(function (d) {
          d.addEventListener('click', function () {
            show(parseInt(d.getAttribute('data-index') || '0', 10));
          });
        });

        root.addEventListener('keydown', function (e) {
          if (data.length < 2) return;
          if (e.key === 'ArrowLeft') {
            e.preventDefault();
            show(idx - 1);
          }
          if (e.key === 'ArrowRight') {
            e.preventDefault();
            show(idx + 1);
          }
        });

        galleries.set(root, show);
        show(0);
      }

      function resetVisibleGallery() {
        const id = location.hash.replace(/^#/, '');
        if (!/^p\d+$/.test(id)) return;
        const section = document.getElementById(id);
        const g = section && section.querySelector('.modal-gallery');
        if (g && galleries.has(g)) {
          galleries.get(g)(0);
          g.focus({ preventScroll: true });
        }
      }

      document.querySelectorAll('.modal-gallery').forEach(initGallery);
      window.addEventListener('hashchange', resetVisibleGallery);
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', resetVisibleGallery);
      } else {
        resetVisibleGallery();
      }
    })();
  </script>
@endpush
