<?php
class Cache {
    private static $cacheDir = __DIR__ . '/../cache/';
    private static $defaultTTL = 3600; // 1 hora
    
    public static function init() {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        
        // Cria .htaccess para proteger pasta cache
        $htaccess = self::$cacheDir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all");
        }
    }
    
    public static function get($key) {
        self::init();
        $filename = self::$cacheDir . md5($key) . '.cache';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            return null;
        }
        
        $data = unserialize($content);
        
        // Verifica se expirou
        if ($data['expires'] < time()) {
            @unlink($filename);
            return null;
        }
        
        return $data['value'];
    }
    
    public static function set($key, $value, $ttl = null) {
        self::init();
        $ttl = $ttl ?? self::$defaultTTL;
        $filename = self::$cacheDir . md5($key) . '.cache';
        
        $data = [
            'expires' => time() + $ttl,
            'value' => $value,
            'created' => time()
        ];
        
        file_put_contents($filename, serialize($data), LOCK_EX);
    }
    
    public static function delete($key) {
        $filename = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($filename)) {
            @unlink($filename);
        }
    }
    
    public static function clear() {
        self::init();
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
    }
    
    public static function remember($key, $callback, $ttl = null) {
        $cached = self::get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    // Limpar cache expirado (executar via cron)
    public static function cleanExpired() {
        self::init();
        $files = glob(self::$cacheDir . '*.cache');
        $deleted = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = unserialize($content);
            
            if ($data['expires'] < time()) {
                @unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
}
