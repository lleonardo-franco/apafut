<?php
/**
 * Watcher de Assets - Minifica automaticamente quando arquivos são modificados
 * Executa: php watch-assets.php
 * 
 * Pressione Ctrl+C para parar
 */

require_once 'src/AssetMinifier.php';

echo "\n🔍 WATCHER DE ASSETS INICIADO\n";
echo "========================================\n";
echo "Monitorando mudanças em:\n";
echo "  • assets/css/*.css\n";
echo "  • assets/js/*.js\n";
echo "========================================\n\n";
echo "💡 Pressione Ctrl+C para parar\n\n";

// Armazenar hashes dos arquivos
$fileHashes = [];

// Função para obter arquivos a monitorar
function getWatchFiles() {
    $files = [];
    
    // CSS files
    $cssFiles = glob(__DIR__ . '/assets/css/*.css');
    foreach ($cssFiles as $file) {
        if (strpos($file, '.min.css') === false) {
            $files[] = $file;
        }
    }
    
    // JS files
    $jsFiles = glob(__DIR__ . '/assets/js/*.js');
    foreach ($jsFiles as $file) {
        if (strpos($file, '.min.js') === false) {
            $files[] = $file;
        }
    }
    
    return $files;
}

// Inicializar hashes
$watchFiles = getWatchFiles();
foreach ($watchFiles as $file) {
    $fileHashes[$file] = md5_file($file);
}

echo "📂 Monitorando " . count($watchFiles) . " arquivos...\n\n";

// Loop de monitoramento
while (true) {
    $watchFiles = getWatchFiles();
    
    foreach ($watchFiles as $file) {
        $currentHash = md5_file($file);
        
        // Arquivo novo ou modificado
        if (!isset($fileHashes[$file]) || $fileHashes[$file] !== $currentHash) {
            $fileName = basename($file);
            $timestamp = date('H:i:s');
            
            echo "[$timestamp] 🔄 Detectada mudança: $fileName\n";
            echo "           🔨 Minificando...\n";
            
            try {
                $result = AssetMinifier::process($file);
                
                if ($result['success']) {
                    echo "           ✅ Sucesso! {$result['originalSize']} → {$result['minifiedSize']} (economia: {$result['savings']})\n\n";
                } else {
                    echo "           ❌ Erro: {$result['error']}\n\n";
                }
            } catch (Exception $e) {
                echo "           ❌ Erro: " . $e->getMessage() . "\n\n";
            }
            
            $fileHashes[$file] = $currentHash;
        }
    }
    
    // Verificar a cada 2 segundos
    sleep(2);
}
