<?php

// send CSP in report-only mode first
// reports policy violations without blocking resources!!
function sendGreenDashCspReportOnly(): void
{
    if (headers_sent()) {
        return;
    }
    header("Content-Security-Policy-Report-Only: "
    . "default-src 'self'; "
    . "base-uri 'self'; "
    . "object-src 'none'; "
    . "frame-ancestors 'self'; "
    . "form-action 'self'; "
    . "script-src 'self' https://accounts.google.com https://cdn.jsdelivr.net; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://accounts.google.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
    . "img-src 'self' data: blob: https://*.googleusercontent.com https://via.placeholder.com; "
    . "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; "
    . "connect-src 'self' https://accounts.google.com https://oauth2.googleapis.com; "
    . "frame-src https://accounts.google.com;");
}
