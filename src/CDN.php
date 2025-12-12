<?php
class CDN {
    private static $enabled = false; // Ativar quando tiver CDN real
    private static $cdnUrl = 'https://cdn.apafut.com.br';
    private static $localUrl = '';
    
    public static function asset($path) {
        // Remove barra inicial se existir
        $path = ltrim($path, '/');
        
        if (!self::$enabled) {
            return '/' . $path;
        }
        
        // Verifica se é asset estático
        $staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'woff', 'woff2', 'ttf', 'eot'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if (in_array($ext, $staticExtensions)) {
            return self::$cdnUrl . '/' . $path;
        }
        
        return '/' . $path;
    }
    
    public static function image($path, $version = null) {
        $url = self::asset($path);
        
        // Adiciona versão para cache busting
        if ($version) {
            $url .= '?v=' . $version;
        }
        
        return $url;
    }
    
    public static function css($path, $version = null) {
        $url = self::asset('assets/css/' . $path);
        
        if ($version) {
            $url .= '?v=' . $version;
        }
        
        return $url;
    }
    
    public static function js($path, $version = null) {
        $url = self::asset('assets/js/' . $path);
        
        if ($version) {
            $url .= '?v=' . $version;
        }
        
        return $url;
    }
    
    public static function enable() {
        self::$enabled = true;
    }
    
    public static function disable() {
        self::$enabled = false;
    }
}
