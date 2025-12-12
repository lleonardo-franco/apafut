<?php
/**
 * Minificador de Assets (CSS e JavaScript)
 * Compressão automática para melhor performance
 */

class AssetMinifier {
    private static $cacheDir = __DIR__ . '/../cache/assets/';
    
    /**
     * Minifica arquivo CSS
     */
    public static function minifyCSS($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo CSS não encontrado: $filePath");
        }
        
        $content = file_get_contents($filePath);
        
        // Remove comentários
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        
        // Remove espaços em branco desnecessários
        $content = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remove espaços ao redor de caracteres especiais
        $content = str_replace([' {', '{ ', ' }', '} ', ' :', ': ', ' ;', '; ', ' ,', ', '], ['{', '{', '}', '}', ':', ':', ';', ';', ',', ','], $content);
        
        // Remove último ponto e vírgula antes de fechar
        $content = preg_replace('/;}/','}',$content);
        
        return trim($content);
    }
    
    /**
     * Minifica arquivo JavaScript
     */
    public static function minifyJS($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo JS não encontrado: $filePath");
        }
        
        $content = file_get_contents($filePath);
        
        // Remove comentários de linha única
        $content = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '', $content);
        
        // Remove comentários multi-linha
        $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);
        
        // Remove quebras de linha e espaços extras
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remove espaços ao redor de operadores
        $content = preg_replace('/\s*([\(\)\{\}\[\];,:])\s*/', '$1', $content);
        
        return trim($content);
    }
    
    /**
     * Processa e minifica arquivo
     */
    public static function process($filePath, $outputPath = null) {
        $pathInfo = pathinfo($filePath);
        $extension = strtolower($pathInfo['extension']);
        
        if ($outputPath === null) {
            $outputPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.min.' . $extension;
        }
        
        try {
            $minified = '';
            
            if ($extension === 'css') {
                $minified = self::minifyCSS($filePath);
            } elseif ($extension === 'js') {
                $minified = self::minifyJS($filePath);
            } else {
                throw new Exception("Tipo de arquivo não suportado: $extension");
            }
            
            // Salvar arquivo minificado
            file_put_contents($outputPath, $minified);
            
            $originalSize = filesize($filePath);
            $minifiedSize = filesize($outputPath);
            $savings = round((($originalSize - $minifiedSize) / $originalSize) * 100, 2);
            
            return [
                'success' => true,
                'original' => $filePath,
                'minified' => $outputPath,
                'originalSize' => self::formatBytes($originalSize),
                'minifiedSize' => self::formatBytes($minifiedSize),
                'savings' => $savings . '%'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Minifica múltiplos arquivos e combina em um só
     */
    public static function combine($files, $outputPath, $type = 'css') {
        $combined = '';
        
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }
            
            if ($type === 'css') {
                $combined .= self::minifyCSS($file) . "\n";
            } elseif ($type === 'js') {
                $combined .= self::minifyJS($file) . ";\n";
            }
        }
        
        file_put_contents($outputPath, $combined);
        
        return [
            'success' => true,
            'files' => count($files),
            'output' => $outputPath,
            'size' => self::formatBytes(filesize($outputPath))
        ];
    }
    
    /**
     * Gera hash do arquivo para cache busting
     */
    public static function generateHash($filePath) {
        if (!file_exists($filePath)) {
            return time();
        }
        return substr(md5_file($filePath), 0, 8);
    }
    
    /**
     * Retorna URL do asset com hash para cache busting
     */
    public static function assetUrl($path) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
        
        if (file_exists($fullPath)) {
            $hash = self::generateHash($fullPath);
            return $path . '?v=' . $hash;
        }
        
        return $path;
    }
    
    /**
     * Formata bytes em KB, MB, etc
     */
    private static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Processa todos arquivos CSS/JS da pasta assets
     */
    public static function processAll() {
        $results = [];
        
        // Processar CSS
        $cssFiles = glob(__DIR__ . '/../assets/css/*.css');
        foreach ($cssFiles as $file) {
            // Pular arquivos já minificados
            if (strpos($file, '.min.css') !== false) {
                continue;
            }
            $results[] = self::process($file);
        }
        
        // Processar JS
        $jsFiles = glob(__DIR__ . '/../assets/js/*.js');
        foreach ($jsFiles as $file) {
            // Pular arquivos já minificados
            if (strpos($file, '.min.js') !== false) {
                continue;
            }
            $results[] = self::process($file);
        }
        
        return $results;
    }
}
