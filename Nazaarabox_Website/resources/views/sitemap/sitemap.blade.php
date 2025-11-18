<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($urls as $url)
    <url>
        <loc>{!! htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') !!}</loc>
        @if(isset($url['lastmod']))
        <lastmod>{!! htmlspecialchars($url['lastmod'], ENT_XML1, 'UTF-8') !!}</lastmod>
        @endif
        @if(isset($url['changefreq']))
        <changefreq>{!! htmlspecialchars($url['changefreq'], ENT_XML1, 'UTF-8') !!}</changefreq>
        @endif
        @if(isset($url['priority']))
        <priority>{!! htmlspecialchars($url['priority'], ENT_XML1, 'UTF-8') !!}</priority>
        @endif
    </url>
    @endforeach
</urlset>

