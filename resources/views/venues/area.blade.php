@extends('layouts.app')

@section('title', $area.'のライブ・イベント会場'.number_format($venues->count()).'件 | '.config('app.name'))
@section('description', $area.'のライブハウス・ホール・スタジアム・展示場'.number_format($venues->count()).'件を一覧で紹介します。会場ごとに最寄り駅と距離つき。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => config('app.name'), 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => $area, 'item' => route('venues.area', $areaSlug)],
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container my-4" style="max-width: 900px;">
  <nav class="small mb-3">
    <a href="{{ route('venues.index') }}">{{ config('app.name') }}</a>
    <span class="text-muted mx-1">/</span><span class="text-muted">{{ $area }}</span>
  </nav>

  <h1 class="h4 fw-bold">{{ $area }}の会場</h1>
  <p class="text-muted">{{ number_format($venues->count()) }}件を掲載しています。</p>

  <div class="row g-3">
    @foreach($venues as $venue)
      <div class="col-12 col-md-6">
        <a href="{{ route('venues.show', $venue['slug']) }}" class="d-block p-3 h-100 text-decoration-none venue-card">
          <span class="badge bg-light text-dark border mb-1">{{ $venue['kind'] }}</span>
          <div class="fw-semibold">{{ $venue['name'] }}</div>
          <div class="small text-muted">
            {{ $venue['area'] }}@if($venue['city'])・{{ $venue['city'] }}@endif
          </div>
          @if($venue['nearestStation'])
            <div class="small text-muted">最寄り駅: {{ $venue['nearestStation'] }}（約{{ $venue['nearestStationKm'] }}km）</div>
          @endif
          @if($venue['capacity'])
            <div class="small text-muted">収容: {{ $venue['capacity'] }}</div>
          @endif
        </a>
      </div>
    @endforeach
  </div>

  <h2 class="h6 mt-5">ほかの都道府県</h2>
  <p class="d-flex flex-wrap gap-2">
    @foreach($areaCounts as $row)
      <a href="{{ route('venues.area', $row['slug']) }}"
         class="btn btn-sm {{ $areaSlug === $row['slug'] ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ $row['area'] }} <span class="text-muted">{{ number_format($row['total']) }}</span>
      </a>
    @endforeach
  </p>
</div>
@endsection
