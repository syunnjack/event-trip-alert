@extends('layouts.app')

@section('title', config('app.name').' | ライブ・スポーツ・展示会の会場'.number_format($total).'件と最寄り駅')
@section('description', '全国'.number_format($total).'件のライブハウス・ホール・スタジアム・展示場を、都道府県と種類から探せます。会場ごとに最寄り駅と距離を掲載。出典はOpenStreetMap。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'WebSite',
  'name' => config('app.name'),
  'url' => url('/'),
  'description' => 'ライブ・スポーツ・展示会の会場を、都道府県と種類から探せるサイト。',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container my-4" style="max-width: 900px;">
  <div class="text-center my-4">
    <h1 class="h3 fw-bold">遠征先の会場と、最寄り駅を調べる</h1>
    <p class="text-muted mb-0">
      全国{{ number_format($total) }}件の会場を掲載しています。会場ごとに最寄り駅と直線距離を出しています。
    </p>
  </div>

  <h2 class="h6 mt-4">会場の種類から探す</h2>
  <div class="row g-2 mb-4">
    @foreach($kindCounts as $row)
      <div class="col-6 col-md-4">
        <a href="{{ route('venues.kind', $row['slug']) }}" class="d-block p-3 text-decoration-none venue-card">
          <div class="fw-semibold">{{ $row['kind'] }}</div>
          <div class="small text-muted">{{ number_format($row['total']) }}件</div>
        </a>
      </div>
    @endforeach
  </div>

  <h2 class="h6">都道府県から探す</h2>
  <p class="d-flex flex-wrap gap-2">
    @foreach($areaCounts as $row)
      <a href="{{ route('venues.area', $row['slug']) }}" class="btn btn-sm btn-outline-secondary">
        {{ $row['area'] }} <span class="text-muted">{{ number_format($row['total']) }}</span>
      </a>
    @endforeach
  </p>

  <p class="text-muted small mt-4">
    掲載しているのは、地図データに会場として登録されている施設です。イベントの開催予定は扱っていません。
    公演情報は各会場・主催者の公式サイトでご確認ください。最寄り駅までの距離は直線距離で、実際の徒歩経路とは異なります。
  </p>
</div>
@endsection
