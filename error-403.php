<?php
/**
 * Standalone 403 Forbidden page.
 *
 * Target of an Apache/LiteSpeed `ErrorDocument 403` directive, e.g.:
 *   ErrorDocument 403 /wp-content/themes/met-hello-elementor-child/error-403.php
 *
 * IMPORTANT: this file is served directly by the web server, so WordPress is NOT
 * loaded here. It must remain pure PHP/HTML with inlined CSS and no WordPress
 * function calls or external asset dependencies.
 */

if ( ! headers_sent() ) {
	http_response_code( 403 );
	header( 'Content-Type: text/html; charset=UTF-8' );
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>403 &mdash; Access Denied</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;}
  html,body{height:100%;margin:0;}
  body{font-family:"Geist",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#0E3B40;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:32px;position:relative;overflow:hidden;-webkit-font-smoothing:antialiased;}
  body::before{content:"";position:absolute;inset:0;background:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="104" viewBox="0 0 60 104"><g fill="none" stroke="%23C99A3A" stroke-width="1" opacity="0.16"><path d="M30 2 L52 15 L52 41 L30 54 L8 41 L8 15 Z"/><path d="M30 50 L52 63 L52 89 L30 102 L8 89 L8 63 Z"/><path d="M0 28 L8 15 M52 15 L60 28 M0 76 L8 63 M52 63 L60 76"/></g></svg>') center/60px 104px;pointer-events:none;}
  .wrap{position:relative;max-width:560px;text-align:center;}
  .brand{font-size:19px;font-weight:700;letter-spacing:0.02em;color:#C99A3A;margin-bottom:40px;}
  .code{font-family:"Instrument Serif",Georgia,serif;font-size:clamp(64px,16vw,120px);line-height:1;color:#C99A3A;margin-bottom:8px;}
  h1{font-size:clamp(24px,4vw,34px);font-weight:700;letter-spacing:-0.02em;margin:0 0 16px;}
  p{font-size:16px;line-height:1.7;color:rgba(255,255,255,0.75);margin:0 auto 32px;max-width:44ch;}
  .btn{display:inline-flex;align-items:center;gap:10px;height:48px;padding:0 26px;border-radius:10px;background:#C99A3A;color:#0E3B40;font-weight:600;font-size:15px;text-decoration:none;transition:transform .18s cubic-bezier(.2,.8,.2,1),background .18s;}
  .btn:hover{background:#B98A2E;transform:translateY(-2px);}
</style>
</head>
<body>
  <div class="wrap">
    <div class="brand">IIUM Holdings Sdn Bhd</div>
    <div class="code">403</div>
    <h1>Access Denied</h1>
    <p>You don&rsquo;t have permission to access this page. If you believe this is an error, please contact the site administrator.</p>
    <a class="btn" href="/">Back to homepage</a>
  </div>
</body>
</html>
