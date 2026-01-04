<?php
/**
 * Script para limpar cache da aplicação
 */
header('Content-Type: text/html; charset=UTF-8');
echo "<h1>Limpeza de Cache</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .erro{color:red;}</style>";

$cacheDir = __DIR__ . '/cache';
$count = 0;
$errors = 0;

if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*');
    
    if (empty($files)) {
        echo "<p class='ok'>✓ A pasta cache/ já está vazia!</p>";
    } else {
        echo "<h2>Arquivos deletados:</h2><ul>";
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if (unlink($file)) {
                    echo "<li class='ok'>✓ " . basename($file) . "</li>";
                    $count++;
                } else {
                    echo "<li class='erro'>✗ Erro ao deletar: " . basename($file) . "</li>";
                    $errors++;
                }
            }
        }
        
        echo "</ul>";
        echo "<p><strong class='ok'>Total de arquivos deletados: {$count}</strong></p>";
        
        if ($errors > 0) {
            echo "<p class='erro'>Erros: {$errors}</p>";
        }
    }
} else {
    echo "<p class='erro'>✗ Pasta cache/ não encontrada!</p>";
}

// Limpar OpCache do PHP se disponível
echo "<h2>OpCache PHP</h2>";
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<p class='ok'>✓ OpCache do PHP limpo com sucesso!</p>";
    } else {
        echo "<p class='erro'>✗ Erro ao limpar OpCache</p>";
    }
} else {
    echo "<p>OpCache não está ativo (normal no XAMPP)</p>";
}

echo "<hr>";
echo "<p><strong>Cache limpo!</strong> Agora acesse:</p>";
echo "<ul>";
echo "<li><a href='admin/'>Painel Admin</a></li>";
echo "<li><a href='index.php'>Site Principal</a></li>";
echo "</ul>";
echo "<p><small>Data: " . date('Y-m-d H:i:s') . "</small></p>";
?>
