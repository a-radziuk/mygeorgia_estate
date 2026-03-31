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
              @php
                $slides = array_map(fn (array $img) => [
                  'src' => asset('assets/'.$img['file']),
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
