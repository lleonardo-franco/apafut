<?php
/**
 * Arquivo de configuração de segurança global e cache inteligente
 * Inclua no início de cada arquivo PHP público
 * 
 * @package APAFUT
 * @version 2.0
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
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

// ====================================
// CACHE CONTROL INTELIGENTE
// ====================================

/**
 * Define estratégia de cache baseada no tipo de página
 * Páginas dinâmicas (PHP) não devem usar cache do navegador
 */

// Detectar se é uma requisição AJAX/API
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Detectar se é área administrativa
$isAdmin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;

if ($isAjax) {
    // APIs e requisições AJAX - cache curto para dados dinâmicos
    header("Cache-Control: public, max-age=300, must-revalidate");
    header("Expires: " . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');
} elseif ($isAdmin) {
    // Área administrativa - sem cache (sempre conteúdo fresco)
    header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0, s-maxage=0");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
} else {
    // Páginas públicas dinâmicas - sem cache (HTML é dinâmico, assets têm versionamento)
    header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
}

// ====================================
// HEADERS ADICIONAIS DE PERFORMANCE
// ====================================

// Prevenir buffering desnecessário
header("X-Accel-Buffering: no");

// Indicar que a resposta pode variar com Accept-Encoding
header("Vary: Accept-Encoding");

