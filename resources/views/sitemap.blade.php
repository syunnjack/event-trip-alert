<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>{{ url('/') }}</loc><priority>1.0</priority></url>
  <url><loc>{{ route('about') }}</loc><priority>0.3</priority></url>
@foreach ($kindSlugs as $kindSlug)
  <url><loc>{{ route('venues.kind', $kindSlug) }}</loc><priority>0.8</priority></url>
@endforeach
@foreach ($areaSlugs as $areaSlug)
  <url><loc>{{ route('venues.area', $areaSlug) }}</loc><priority>0.8</priority></url>
@endforeach
@foreach ($venues as $venue)
  <url><loc>{{ route('venues.show', $venue['slug']) }}</loc><priority>0.6</priority></url>
@endforeach
</urlset>
