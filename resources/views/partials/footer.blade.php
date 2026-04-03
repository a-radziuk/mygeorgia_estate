<footer class="site-footer">
  <div class="container footer-inner">
    <div>
      <b>{{ $site['brand']['title'] }}</b>
      <div class="muted" style="margin-top:.25rem;">{{ $site['footer'][($page === 'listing' ? 'properties' : $page)]['tagline'] ?? $site['footer']['home']['tagline'] }}</div>
    </div>
    <div class="footer-links" aria-label="Footer links">
      <a href="{{ route('site.properties', ['locale' => $locale, 'city' => $city, 'type' => 'apartment']) }}">{{ $site['nav']['apartments'] }}</a>
      <a href="{{ route('site.properties', ['locale' => $locale, 'city' => $city, 'type' => 'house']) }}">{{ $site['nav']['houses'] }}</a>
      <a href="{{ route('site.about', ['locale' => $locale, 'city' => $city]) }}">{{ $site['nav']['about'] }}</a>
      <a href="{{ route('site.faqs', ['locale' => $locale, 'city' => $city]) }}">{{ $site['nav']['faq'] }}</a>
      <a href="{{ route('site.contact', ['locale' => $locale, 'city' => $city]) }}">{{ $site['nav']['contact'] }}</a>
    </div>
  </div>
</footer>
