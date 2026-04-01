<!doctype html>
<html lang="{{ $site['html_lang'] }}">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')"/>
    <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}"/>
  </head>
  <body>
    @include('partials.header')

    <main id="main" class="container">
      @yield('content')
    </main>

    @include('partials.footer')
    @stack('scripts')
  </body>
</html>
