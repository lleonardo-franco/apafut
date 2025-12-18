<?php
/**
 * Script de Minificação Automática de Assets
 * Executa: php minify-assets.php
 */

require_once 'src/AssetMinifier.php';

echo "\n========================================\n";
echo "  MINIFICADOR AUTOMÁTICO DE ASSETS\n";
echo "========================================\n\n";

try {
    $results = AssetMinifier::processAll();
    
    $totalOriginal = 0;
    $totalMinified = 0;
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($results as $result) {
        if ($result['success']) {
            $successCount++;
            echo "✓ {$result['original']}\n";
            echo "  → {$result['minified']}\n";
            echo "  📦 {$result['originalSize']} → {$result['minifiedSize']} (economia: {$result['savings']})\n\n";
        } else {
            $errorCount++;
            echo "✗ Erro: {$result['error']}\n\n";
        }
    }
    
    echo "========================================\n";
    echo "📊 RESUMO\n";
    echo "========================================\n";
    echo "✓ Sucesso: $successCount arquivos\n";
    echo "✗ Erros: $errorCount arquivos\n";
    echo "\n✅ Minificação concluída!\n\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n\n";
    exit(1);
}
