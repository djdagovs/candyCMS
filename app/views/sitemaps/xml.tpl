{strip}
  <?xml version="1.0" encoding="UTF-8"?>
  <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
      <loc>{$_website_landing_page_}</loc>
      <priority>1.0</priority>
      <changefreq>hourly</changefreq>
    </url>
    <url>
      <loc>{$WEBSITE_URL}/newsletter</loc>
      <priority>0.1</priority>
      <changefreq>never</changefreq>
    </url>
    <url>
      <loc>{$WEBSITE_URL}/sitemap</loc>
      <priority>0.75</priority>
      <changefreq>daily</changefreq>
    </url>
    <url>
      <loc>{$WEBSITE_URL}/search</loc>
      <priority>0.1</priority>
      <changefreq>never</changefreq>
    </url>
    <url>
      <loc>{$WEBSITE_URL}/session/create</loc>
      <priority>0.1</priority>
      <changefreq>never</changefreq>
    </url>
    <url>
      <loc>{$WEBSITE_URL}/session/password</loc>
      <priority>0.1</priority>
      <changefreq>never</changefreq>
    </url>
    <url>
      <loc>{$WEBSITE_URL}/session/verification</loc>
      <priority>0.1</priority>
      <changefreq>never</changefreq>
    </url>
    <url>
      <loc>{$WEBSITE_URL}/user/create</loc>
      <priority>0.1</priority>
      <changefreq>never</changefreq>
    </url>
    {foreach $blog as $b}
      <url>
        <loc>{$b.url}</loc>
        <priority>{$b.priority}</priority>
        <changefreq>{$b.changefreq}</changefreq>
      </url>
    {/foreach}
    {foreach $content as $c}
      <url>
        <loc>{$c.url}</loc>
        <priority>{$c.priority}</priority>
        <changefreq>{$c.changefreq}</changefreq>
      </url>
    {/foreach}
    {foreach $gallery as $g}
      <url>
        <loc>{$g.url}</loc>
        <priority>{$g.priority}</priority>
        <changefreq>{$g.changefreq}</changefreq>
      </url>
    {/foreach}
  </urlset>
{/strip}