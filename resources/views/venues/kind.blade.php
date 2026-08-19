@extends('layouts.app')

@section('title', $kind.'の一覧'.number_format($venues->count()).'件 | '.config('app.name'))
@section('description', '全国の'.$kind.number_format($venues->count()).'件を都道府県順に並べました。会場ごとに最寄り駅と距離つき。')

@section('content')
<div class="container my-4" style="max-width: 900px;">
  <nav class="small mb-3">
    <a href="{{ route('venues.index') }}">{{ config('app.name') }}</a>
    <span class="text-muted mx-1">/</span><span class="text-muted">{{ $kind }}</span>
  </nav>

  <h1 class="h4 fw-bold">{{ $kind }}</h1>
  <p class="text-muted">全国{{ number_format($venues->count()) }}件</p>

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
</div>
@endsection
