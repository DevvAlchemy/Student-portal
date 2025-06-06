<?php
// Set security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Content Security Policy
$csp = "default-src 'self'; ";
$csp .= "script-src 'self' 'unsafe-inline'; ";
$csp .= "style-src 'self' 'unsafe-inline'; ";
$csp .= "img-src 'self' data:; ";
$csp .= "font-src 'self'; ";
$csp .= "connect-src 'self'; ";
$csp .= "frame-ancestors 'none'; ";
$csp .= "base-uri 'self'; ";
$csp .= "form-action 'self'";

header("Content-Security-Policy: $csp");

// Prevent caching of sensitive pages
function preventCache() {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
}
?>