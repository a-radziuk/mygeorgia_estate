@extends('layouts.site')

@php
  $p = $site['pages']['properties'];
  $backToProperties = route('site.properties', array_filter([
    'locale' => $locale,
    'page' => request()->integer('return_page', 1) > 1 ? request('return_page') : null,
  ], fn ($v) => $v !== null && $v !== ''));
@endphp

@section('title', $listing['modal_title'].' | '.$site['brand']['title'])
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) ($listing['address'] ?? '')), 158))

@section('content')
  <div id="top" aria-hidden="true"></div>

  <article class="listing-page" aria-labelledby="listing-title">
    <div class="listing-page-inner">
      <header class="listing-page-header">
        <h1 id="listing-title" class="listing-page-title">{{ $listing['modal_title'] }}</h1>
        <a class="close-link" href="{{ $backToProperties }}#property-grid">{{ $p['back_listings'] }}</a>
      </header>

      <div class="modal-body listing-page-body">
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
                  loading="eager"
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
            <a class="btn btn-ghost" href="{{ $backToProperties }}#property-grid">{{ $p['back_listings'] }}</a>
          </div>
          <p class="muted" style="margin: .85rem 0 0;">{!! $listing['tip'] !!}</p>
        </div>
      </div>
    </div>
  </article>
@endsection

@push('scripts')
  <script>
    (function () {
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

        show(0);
      }

      document.querySelectorAll('.listing-page .modal-gallery').forEach(initGallery);
    })();
  </script>
@endpush
