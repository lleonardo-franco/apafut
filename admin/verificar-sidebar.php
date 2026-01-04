<?php
// Verificar conteúdo exato do sidebar.php
header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTE SIDEBAR.PHP ===\n\n";
echo "Caminho: " . __DIR__ . "/includes/sidebar.php\n";
echo "Existe: " . (file_exists(__DIR__ . "/includes/sidebar.php") ? "SIM" : "NÃO") . "\n\n";

if (file_exists(__DIR__ . "/includes/sidebar.php")) {
    echo "Última modificação: " . date("Y-m-d H:i:s", filemtime(__DIR__ . "/includes/sidebar.php")) . "\n";
    echo "Tamanho: " . filesize(__DIR__ . "/includes/sidebar.php") . " bytes\n\n";
    echo "=== CONTEÚDO COMPLETO ===\n\n";
    echo file_get_contents(__DIR__ . "/includes/sidebar.php");
    
    echo "\n\n=== BUSCAR 'COMISSÃO' NO ARQUIVO ===\n";
    $conteudo = file_get_contents(__DIR__ . "/includes/sidebar.php");
    if (stripos($conteudo, 'comissao') !== false) {
        echo "✓ ENCONTRADO: A palavra 'comissao' está no arquivo!\n";
        
        // Mostrar linhas que contém comissao
        $linhas = explode("\n", $conteudo);
        foreach ($linhas as $num => $linha) {
            if (stripos($linha, 'comissao') !== false) {
                echo "Linha " . ($num + 1) . ": " . trim($linha) . "\n";
            }
        }
    } else {
        echo "✗ NÃO ENCONTRADO: A palavra 'comissao' NÃO está no arquivo!\n";
    }
}
?>
