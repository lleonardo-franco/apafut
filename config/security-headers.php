<?php
/**
 * Arquivo de configuração de segurança global
 * Inclua no início de cada arquivo PHP público
 */

// Headers de segurança
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Content Security Policy
$csp = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://kit.fontawesome.com",
    "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://kit.fontawesome.com https://ka-f.fontawesome.com",
    "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com https://kit.fontawesome.com https://ka-f.fontawesome.com",
    "img-src 'self' data: https:",
    "connect-src 'self' https://ka-f.fontawesome.com https://viacep.com.br",
    "frame-ancestors 'self'"
];
header("Content-Security-Policy: " . implode("; ", $csp));

// HSTS (somente em produção com HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// Cache control para páginas dinâmicas
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
