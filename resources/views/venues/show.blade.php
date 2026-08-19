@extends('layouts.app')

@section('title', $venue['name'].'（'.$venue['area'].'の'.$venue['kind'].'）の場所と最寄り駅 | '.config('app.name'))
@section('description', $venue['name'].'は'.$venue['area'].'の'.$venue['kind'].'です。'.($venue['nearestStation'] ? '最寄り駅は'.$venue['nearestStation'].'（直線約'.$venue['nearestStationKm'].'km）。' : '').'場所と連絡先を掲載しています。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => config('app.name'), 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => $venue['area'], 'item' => route('venues.area', $venue['area_slug'])],
      ['@type' => 'ListItem', 'position' => 3, 'name' => $venue['name'], 'item' => route('venues.show', $venue['slug'])],
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode(array_filter([
  '@@context' => 'https://schema.org',
  '@type' => 'Place',
  'name' => $venue['name'],
  'address' => $venue['address'] ?? $venue['area'],
  'telephone' => $venue['phone'],
  'geo' => ['@type' => 'GeoCoordinates', 'latitude' => $venue['lat'], 'longitude' => $venue['lng']],
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container my-4" style="max-width: 760px;">
  <nav class="small mb-3">
    <a href="{{ route('venues.index') }}">{{ config('app.name') }}</a>
    <span class="text-muted mx-1">/</span>
    <a href="{{ route('venues.area', $venue['area_slug']) }}">{{ $venue['area'] }}</a>
    <span class="text-muted mx-1">/</span><span class="text-muted">{{ $venue['name'] }}</span>
  </nav>

  <h1 class="h3 fw-bold">{{ $venue['name'] }}</h1>
  <p class="text-muted">
    <a href="{{ route('venues.kind', $venue['kind_slug']) }}">{{ $venue['kind'] }}</a>・{{ $venue['area'] }}
  </p>

  <table class="table bg-white">
    <tbody>
      @if($venue['nearestStation'])
      <tr>
        <th class="text-muted small" style="width:11rem;">最寄り駅</th>
        <td>{{ $venue['nearestStation'] }}<span class="text-muted small">（直線 約{{ $venue['nearestStationKm'] }}km）</span></td>
      </tr>
      @endif
      @if($venue['address'])
      <tr><th class="text-muted small">住所</th><td>{{ $venue['address'] }}</td></tr>
      @elseif($venue['city'])
      <tr><th class="text-muted small">市区町村</th><td>{{ $venue['city'] }}</td></tr>
      @endif
      @if($venue['capacity'])
      <tr><th class="text-muted small">収容人数</th><td>{{ $venue['capacity'] }}</td></tr>
      @endif
      @if($venue['operator'])
      <tr><th class="text-muted small">運営</th><td>{{ $venue['operator'] }}</td></tr>
      @endif
      @if($venue['phone'])
      <tr><th class="text-muted small">電話</th><td>{{ $venue['phone'] }}</td></tr>
      @endif
      @if($venue['website'])
      <tr><th class="text-muted small">公式サイト</th><td><a href="{{ $venue['website'] }}" rel="nofollow noopener" target="_blank">{{ $venue['website'] }}</a></td></tr>
      @endif
      <tr>
        <th class="text-muted small">地図</th>
        <td>
          <a href="https://www.openstreetmap.org/{{ $venue['sourceRef'] }}" rel="nofollow noopener" target="_blank">OpenStreetMapで見る</a>
          <span class="text-muted small">（{{ $venue['lat'] }}, {{ $venue['lng'] }}）</span>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="alert alert-light border small">
    この会場の情報は OpenStreetMap のデータ（&copy; OpenStreetMap contributors、ODbL 1.0）をもとに掲載しています。
    最寄り駅までの距離は直線距離で、実際の徒歩経路とは異なります。
    公演の予定・チケット・アクセスの詳細は、会場や主催者の公式サイトでご確認ください。
  </div>

  @if($others->isNotEmpty())
    <h2 class="h6 mt-4">{{ $venue['area'] }}のほかの会場</h2>
    <div class="d-flex flex-wrap gap-2">
      @foreach($others as $other)
        <a href="{{ route('venues.show', $other['slug']) }}" class="btn btn-sm btn-outline-secondary">{{ $other['name'] }}</a>
      @endforeach
    </div>
    <p class="mt-3"><a href="{{ route('venues.area', $venue['area_slug']) }}">{{ $venue['area'] }}の会場をすべて見る</a></p>
  @endif
</div>
@endsection
