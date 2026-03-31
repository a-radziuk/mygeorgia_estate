<!doctype html>
<html lang="{{ $site['html_lang'] }}">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')"/>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}"/>
  </head>
  <body>
    @include('partials.header')

    <main id="main" class="container">
      @yield('content')
    </main>

    @include('partials.footer')
  </body>
</html>
