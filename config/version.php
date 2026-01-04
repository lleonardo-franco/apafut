<?php
/**
 * Sistema de Versionamento de Assets
 * Atualizar este arquivo (touch) a cada deploy para forçar refresh do cache do navegador
 * 
 * @package APAFUT
 * @version 2.0
 */

// Versão baseada na data de modificação deste arquivo
define('ASSET_VERSION', '2.0.' . filemtime(__FILE__));

/**
 * Gera URL de asset com parâmetro de versão para cache busting
 * 
 * @param string $path Caminho relativo do asset
 * @return string URL completa com parâmetro de versão
 * 
 * @example asset_url('assets/css/style.css') retorna 'assets/css/style.css?v=2.0.1234567890'
 */
function asset_url($path) {
    $version = ASSET_VERSION;
    $separator = (strpos($path, '?') !== false) ? '&' : '?';
    return $path . $separator . 'v=' . $version;
}

/**
 * Define headers de cache HTTP apropriados baseado no tipo de conteúdo
 * 
 * @param string $type Tipo de conteúdo: 'html', 'static', 'api', 'default'
 * @return void
 */
function set_cache_headers($type = 'default') {
    $headers = [
        // HTML - Sem cache para sempre ter conteúdo atualizado
        'html' => [
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ],
        
        // Assets estáticos (CSS, JS, Imagens) - Cache longo com versionamento
        'static' => [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT'
        ],
        
        // APIs e dados dinâmicos - Cache curto
        'api' => [
            'Cache-Control' => 'public, max-age=300, must-revalidate',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 300) . ' GMT'
        ],
        
        // Padrão - Cache moderado
        'default' => [
            'Cache-Control' => 'public, max-age=3600',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT'
        ]
    ];
    
    $selected = $headers[$type] ?? $headers['default'];
    
    foreach ($selected as $header => $value) {
        header("$header: $value");
    }
}

/**
 * Limpa o cache do navegador forçando headers de no-cache
 * Útil para páginas administrativas ou após logout
 * 
 * @return void
 */
function force_no_cache() {
    header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0, s-maxage=0");
    header("Pragma: no-cache");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
}

/**
 * Retorna a versão atual dos assets
 * 
 * @return string Versão atual
 */
function get_asset_version() {
    return ASSET_VERSION;
}
