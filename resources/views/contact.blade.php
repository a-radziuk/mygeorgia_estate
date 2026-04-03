@extends('layouts.site')

@section('title', $site['meta']['contact']['title'])
@section('description', $site['meta']['contact']['description'])

@section('content')
  @php $p = $site['pages']['contact']; @endphp

  <section class="hero" style="margin-bottom: 1.4rem;">
    <div class="hero-card">
      <div class="eyebrow"><span class="dot" aria-hidden="true"></span> {{ $p['hero_eyebrow'] }}</div>
      <h1>{{ $p['hero_title'] }}</h1>
      <p>{{ $p['hero_text'] }}</p>
      <div class="actions">
        <a class="btn btn-primary" href="{{ route('site.properties', ['locale' => $locale, 'city' => $city]) }}#top">{{ $p['cta_browse'] }}</a>
        <a class="btn btn-ghost" href="#contact-form">{{ $p['cta_form'] }}</a>
      </div>
    </div>

    <aside class="hero-aside">
      <b>{{ $p['aside_title'] }}</b>
      <div class="meta-row" style="margin-top: .9rem;">
        @foreach ($p['chips'] as $chip)
          <span class="chip">{{ $chip }}</span>
        @endforeach
      </div>
      <div class="notice" style="margin-top: 1rem;">{{ $p['hours_notice'] }}</div>
    </aside>
  </section>

  <section class="section">
    <div class="two-col">
      <div class="card">
        <div class="card-body">
          <b>{{ $p['form_title'] }}</b>
          <p class="muted" style="margin: .45rem 0 0;">{!! $p['form_intro'] !!}</p>

          <form action="#" method="post" id="contact-form" aria-label="{{ $p['form_aria'] }}">
            <div class="field">
              <label for="name">{{ $p['labels']['name'] }}</label>
              <input id="name" name="name" placeholder="{{ $p['placeholders']['name'] }}" required/>
            </div>
            <div class="field">
              <label for="email">{{ $p['labels']['email'] }}</label>
              <input id="email" name="email" type="email" placeholder="{{ $p['placeholders']['email'] }}" required/>
            </div>
            <div class="field">
              <label for="phone">{{ $p['labels']['phone'] }}</label>
              <input id="phone" name="phone" placeholder="{{ $p['placeholders']['phone'] }}"/>
            </div>
            <div class="field">
              <label for="city">{{ $p['labels']['city'] }}</label>
              <select id="city" name="city">
                @foreach ($site['contact_cities'] as $value => $label)
                  <option value="{{ $value }}" @if($value === 'Any') selected @endif>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label for="budget">{{ $p['labels']['budget'] }}</label>
              <select id="budget" name="budget">
                @foreach ($site['contact_budgets'] as $value => $label)
                  <option value="{{ $value }}" @if($value === 'Any') selected @endif>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label for="propertyCode">{{ $p['labels']['code'] }}</label>
              <input id="propertyCode" name="propertyCode" placeholder="{{ $p['placeholders']['code'] }}"/>
            </div>
            <div class="field">
              <label for="message">{{ $p['labels']['message'] }}</label>
              <textarea id="message" name="message" placeholder="{{ $p['placeholders']['message'] }}"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">{{ $p['submit'] }}</button>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <b>{{ $p['office_title'] }}</b>
          <p class="muted" style="margin: .45rem 0 0;">{{ $p['office_text'] }}</p>

          <div class="notice" style="height: 220px; display:flex; align-items:center; justify-content:center; text-align:center; margin-top: 1rem;">
            {{ $p['map_placeholder'] }}
          </div>

          <div class="section" style="margin-top: 1.25rem;">
            <b>{{ $p['next_title'] }}</b>
            <div class="bullets" style="margin-top: .8rem;">
              @foreach ($p['next_steps'] as $step)
                <div class="notice" style="margin-top: .7rem;">{{ $step }}</div>
              @endforeach
            </div>
          </div>

          <p class="muted" style="margin-top: 1rem;">{{ $p['form_reminder'] }}</p>
        </div>
      </div>
    </div>
  </section>
@endsection
