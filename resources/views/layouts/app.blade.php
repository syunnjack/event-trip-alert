<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#1f3d5c">

  <title>@yield('title', config('app.name').' | ライブ・スポーツ・展示会の会場と最寄り駅')</title>
  <meta name="description" content="@yield('description', 'ライブ・スポーツ・展示会の会場を、都道府県と種類から探せます。会場ごとに最寄り駅と距離を掲載しています。')">

  @php
      $canonicalQuery = array_filter(request()->only(['page']), fn ($value) => $value !== null && $value !== '' && $value !== '1');
      $canonicalUrl = url()->current().($canonicalQuery ? '?'.http_build_query($canonicalQuery) : '');
  @endphp
  <link rel="canonical" href="{{ $canonicalUrl }}">

  <meta property="og:site_name" content="{{ config('app.name') }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('title', config('app.name'))">
  <meta property="og:description" content="@yield('description', 'ライブ・スポーツ・展示会の会場を、都道府県と種類から探せます。')">
  <meta property="og:url" content="{{ $canonicalUrl }}">
  <meta property="og:locale" content="ja_JP">
  <meta name="twitter:card" content="summary">

  @if(config('services.google_site_verification'))
  <meta name="google-site-verification" content="{{ config('services.google_site_verification') }}">
  @endif

  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background:#f7f9fb; color:#1c2733; font-family: system-ui, -apple-system, "Hiragino Sans", "Noto Sans JP", sans-serif; }
    a { color:#1f3d5c; }
    .venue-card { background:#fff; border:1px solid #dfe6ee; border-radius:.5rem; }
    .venue-card:hover { border-color:#9db6cd; }
  </style>
  @stack('structured-data')

  @if(config('services.ga_measurement_id'))
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga_measurement_id') }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.ga_measurement_id') }}');
  </script>
  @endif
</head>
<body>
<header class="bg-white border-bottom">
  <div class="container py-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('venues.index') }}" class="fs-5 fw-bold text-decoration-none">🎫 {{ config('app.name') }}</a>
    <a href="{{ route('about') }}" class="small">このサイトについて</a>
  </div>
</header>

@yield('content')

<footer class="bg-white border-top mt-5 py-4">
  <div class="container small text-muted">
    <p class="mb-1">
      会場の名称・場所・最寄り駅は
      <a href="{{ $meta['sourceUrl'] ?? 'https://www.openstreetmap.org/copyright' }}" rel="nofollow noopener" target="_blank">{{ $meta['sourceLabel'] ?? 'OpenStreetMap contributors（ODbL 1.0）' }}</a>
      のデータをもとにしています。
    </p>
    <p class="mb-0">
      <a href="{{ route('about') }}" class="me-3">このサイトについて</a>
      &copy; {{ date('Y') }} {{ config('app.name') }}
    </p>
  </div>
</footer>
</body>
</html>
