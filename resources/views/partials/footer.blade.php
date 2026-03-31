<footer class="site-footer">
  <div class="container footer-inner">
    <div>
      <b>{{ $site['brand']['title'] }}</b>
      <div class="muted" style="margin-top:.25rem;">{{ $site['footer'][$page]['tagline'] ?? $site['footer']['home']['tagline'] }}</div>
    </div>
    <div class="footer-links" aria-label="Footer links">
      <a href="{{ route('site.properties', ['locale' => $locale]) }}">{{ $site['nav']['properties'] }}</a>
      <a href="{{ route('site.about', ['locale' => $locale]) }}">{{ $site['nav']['about'] }}</a>
      <a href="{{ route('site.faqs', ['locale' => $locale]) }}">{{ $site['nav']['faq'] }}</a>
      <a href="{{ route('site.contact', ['locale' => $locale]) }}">{{ $site['nav']['contact'] }}</a>
    </div>
  </div>
</footer>
