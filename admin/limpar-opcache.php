<?php
/**
 * Script para limpar OPcache do PHP
 * Use quando fizer alterações em arquivos PHP e elas não aparecerem
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Limpar OPcache</title>
    <link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    ";
echo "<style>body{font-family:Arial;padding:40px;background:#f5f5f5;}";
echo ".box{background:white;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);max-width:600px;margin:0 auto;}";
echo "h1{color:#333;margin-bottom:20px;}";
echo ".success{background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:15px;border-radius:5px;margin:15px 0;}";
echo ".info{background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;padding:15px;border-radius:5px;margin:15px 0;}";
echo ".warning{background:#fff3cd;border:1px solid #ffeaa7;color:#856404;padding:15px;border-radius:5px;margin:15px 0;}";
echo ".btn{display:inline-block;background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-top:20px;}";
echo "</style></head><body><div class='box'>";

echo "<h1>🔄 Limpar Cache do PHP (OPcache)</h1>";

// Verificar se OPcache está habilitado
if (function_exists('opcache_reset')) {
    echo "<div class='info'><strong>Status OPcache:</strong> Habilitado ✓</div>";
    
    // Resetar OPcache
    if (opcache_reset()) {
        echo "<div class='success'><strong>✓ SUCESSO!</strong><br>";
        echo "OPcache foi limpo completamente.<br>";
        echo "Todas as páginas PHP serão recarregadas do disco.</div>";
        
        // Estatísticas
        $status = opcache_get_status();
        if ($status) {
            echo "<div class='info'>";
            echo "<strong>Estatísticas:</strong><br>";
            echo "• Memória usada: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB<br>";
            echo "• Memória livre: " . round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB<br>";
            echo "• Scripts em cache: " . $status['opcache_statistics']['num_cached_scripts'] . "<br>";
            echo "• Taxa de acertos: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%";
            echo "</div>";
        }
    } else {
        echo "<div class='warning'><strong>⚠ AVISO</strong><br>";
        echo "Não foi possível resetar o OPcache.<br>";
        echo "Pode ser necessário reiniciar o Apache.</div>";
    }
} else {
    echo "<div class='warning'><strong>⚠ AVISO</strong><br>";
    echo "OPcache não está habilitado ou não disponível nesta instalação do PHP.<br>";
    echo "O cache de código compilado não está sendo usado.</div>";
}

// Limpar também file cache se existir
$cacheDir = dirname(__DIR__) . '/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*');
    $deletedCount = 0;
    foreach ($files as $file) {
        if (is_file($file) && unlink($file)) {
            $deletedCount++;
        }
    }
    if ($deletedCount > 0) {
        echo "<div class='success'><strong>✓ Cache de arquivos limpo</strong><br>";
        echo "$deletedCount arquivo(s) deletado(s) da pasta cache/</div>";
    }
}

echo "<div class='info'><strong>Próximo passo:</strong><br>";
echo "1. Pressione Ctrl+F5 no seu navegador para recarregar sem cache<br>";
echo "2. Ou abra uma aba anônima/privada<br>";
echo "3. Acesse novamente o painel admin</div>";

echo "<a href='dashboard.php' class='btn'>« Voltar ao Dashboard</a>";

echo "</div></body></html>";
?>
