<?php
/**
 * WordPress maintenance drop-in.
 *
 * Copy this file to wp-content/maintenance.php. WordPress then shows it while
 * core, plugins or themes are updating. It is not part of the theme folder and
 * must be placed manually on each deploy.
 *
 * IMPORTANT: WordPress loads this very early during an upgrade, so the theme is
 * not available and most WordPress functions are not loaded. Keep it pure
 * PHP/HTML with inlined CSS and no WordPress function calls. It matches
 * error-403.php and the theme's maintenance page by design, not by shared code.
 *
 * For the theme's own maintenance toggle, which does run with WordPress loaded,
 * see MET_HELLO_CHILD_MAINTENANCE in readme.txt.
 */

if ( ! headers_sent() ) {
	http_response_code( 503 );
	header( 'Retry-After: 3600' );
	header( 'Content-Type: text/html; charset=UTF-8' );
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Scheduled maintenance</title>
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
  h1{font-family:"Instrument Serif",Georgia,serif;font-size:clamp(30px,6vw,52px);line-height:1.1;color:#C99A3A;font-weight:400;margin:0 0 16px;}
  p{font-size:16px;line-height:1.7;color:rgba(255,255,255,0.75);margin:0 auto;max-width:44ch;}
</style>
</head>
<body>
  <div class="wrap">
    <div class="brand">IIUM Holdings Sdn Bhd</div>
    <h1>We&rsquo;ll be right back</h1>
    <p>The site is briefly unavailable while we apply a scheduled update. Please check back in a few minutes.</p>
  </div>
</body>
</html>
