<?php
class ImageOptimizer {
    private static $uploadDir = __DIR__ . '/../assets/images/';
    private static $webpDir = __DIR__ . '/../assets/images/webp/';
    private static $thumbDir = __DIR__ . '/../assets/images/thumbs/';
    
    public static function init() {
        $dirs = [self::$uploadDir, self::$webpDir, self::$thumbDir];
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    public static function optimize($imagePath, $quality = 85) {
        self::init();
        
        if (!file_exists($imagePath)) {
            return false;
        }
        
        $info = pathinfo($imagePath);
        $ext = strtolower($info['extension']);
        
        // Carrega imagem
        $image = null;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $image = @imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $image = @imagecreatefromgif($imagePath);
                break;
            default:
                return false;
        }
        
        if (!$image) {
            return false;
        }
        
        $result = [
            'original' => $imagePath,
            'webp' => null,
            'thumbnail' => null,
            'responsive' => []
        ];
        
        // Gera WebP
        if (function_exists('imagewebp')) {
            $webpPath = self::$webpDir . $info['filename'] . '.webp';
            imagewebp($image, $webpPath, $quality);
            $result['webp'] = $webpPath;
        }
        
        // Gera thumbnail
        $result['thumbnail'] = self::createThumbnail($imagePath, 300, 300);
        
        // Gera versões responsivas
        $result['responsive'] = self::generateResponsive($imagePath);
        
        imagedestroy($image);
        
        return $result;
    }
    
    private static function createThumbnail($source, $maxWidth, $maxHeight) {
        $info = pathinfo($source);
        list($width, $height) = getimagesize($source);
        
        // Calcula proporção
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        $image = imagecreatefromjpeg($source);
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        $thumbPath = self::$thumbDir . $info['filename'] . '_thumb.jpg';
        imagejpeg($thumb, $thumbPath, 85);
        
        imagedestroy($image);
        imagedestroy($thumb);
        
        return $thumbPath;
    }
    
    private static function generateResponsive($source) {
        $sizes = [320, 640, 960, 1280];
        $info = pathinfo($source);
        $responsive = [];
        
        list($origWidth, $origHeight) = getimagesize($source);
        
        foreach ($sizes as $width) {
            if ($width >= $origWidth) continue;
            
            $ratio = $width / $origWidth;
            $height = round($origHeight * $ratio);
            
            $image = imagecreatefromjpeg($source);
            $resized = imagecreatetruecolor($width, $height);
            
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
            
            $outputPath = self::$uploadDir . $info['filename'] . '-' . $width . 'w.jpg';
            imagejpeg($resized, $outputPath, 85);
            
            $responsive[$width] = $outputPath;
            
            imagedestroy($image);
            imagedestroy($resized);
        }
        
        return $responsive;
    }
    
    public static function getResponsiveHTML($imagePath, $alt = '', $class = '', $loading = 'lazy') {
        $info = pathinfo($imagePath);
        $filename = $info['filename'];
        $baseDir = dirname($imagePath);
        
        $srcset = [];
        $sizes = [320, 640, 960, 1280];
        
        foreach ($sizes as $width) {
            $path = "{$baseDir}/{$filename}-{$width}w.jpg";
            if (file_exists($path)) {
                $srcset[] = "/{$path} {$width}w";
            }
        }
        
        $srcsetStr = implode(', ', $srcset);
        $webpPath = self::$webpDir . $filename . '.webp';
        
        $alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
        
        if (file_exists($webpPath)) {
            return <<<HTML
<picture>
    <source type="image/webp" srcset="/{$webpPath}">
    <source type="image/jpeg" srcset="{$srcsetStr}">
    <img src="{$imagePath}" 
         srcset="{$srcsetStr}"
         sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
         alt="{$alt}" 
         class="{$class}"
         loading="{$loading}">
</picture>
HTML;
        }
        
        return "<img src=\"{$imagePath}\" srcset=\"{$srcsetStr}\" alt=\"{$alt}\" class=\"{$class}\" loading=\"{$loading}\">";
    }
}
