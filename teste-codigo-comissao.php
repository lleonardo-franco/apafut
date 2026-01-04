<?php
/**
 * TESTE - Verificar código da comissão técnica
 */
header('Content-Type: text/html; charset=UTF-8');
echo "<h1>Teste de Código - Comissão Técnica</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .erro{color:red;} pre{background:#f5f5f5;padding:10px;overflow:auto;}</style>";

echo "<h2>1. Verificando index.php</h2>";
if (file_exists('index.php')) {
    $conteudo = file_get_contents('index.php');
    
    // Verifica se busca comissão técnica
    if (strpos($conteudo, 'comissao_tecnica') !== false) {
        echo "✓ <span class='ok'>Código de busca da comissão encontrado</span><br>";
    } else {
        echo "✗ <span class='erro'>Código de busca da comissão NÃO encontrado!</span><br>";
        echo "<p><strong>PROBLEMA:</strong> O index.php não tem o código para buscar a comissão</p>";
    }
    
    // Verifica se tem as abas
    if (strpos($conteudo, 'profissional-tabs') !== false) {
        echo "✓ <span class='ok'>HTML das abas encontrado</span><br>";
    } else {
        echo "✗ <span class='erro'>HTML das abas NÃO encontrado!</span><br>";
        echo "<p><strong>PROBLEMA:</strong> O index.php não tem as abas</p>";
    }
    
    // Verifica se tem a seção comissão
    if (strpos($conteudo, 'comissao-section') !== false || strpos($conteudo, 'comissao-container') !== false) {
        echo "✓ <span class='ok'>Seção da comissão encontrada</span><br>";
    } else {
        echo "✗ <span class='erro'>Seção da comissão NÃO encontrada!</span><br>";
        echo "<p><strong>PROBLEMA:</strong> O index.php não tem o HTML da comissão</p>";
    }
    
    // Verifica se tem modal da comissão
    if (strpos($conteudo, 'modalComissao') !== false) {
        echo "✓ <span class='ok'>Modal da comissão encontrado</span><br>";
    } else {
        echo "✗ <span class='erro'>Modal da comissão NÃO encontrado!</span><br>";
    }
    
} else {
    echo "✗ <span class='erro'>index.php não encontrado!</span><br>";
}

echo "<h2>2. Verificando CSS (style.min.css)</h2>";
if (file_exists('assets/css/style.min.css')) {
    $css = file_get_contents('assets/css/style.min.css');
    
    if (strpos($css, 'profissional-tabs') !== false) {
        echo "✓ <span class='ok'>CSS das abas encontrado</span><br>";
    } else {
        echo "✗ <span class='erro'>CSS das abas NÃO encontrado!</span><br>";
        echo "<p><strong>PROBLEMA:</strong> O CSS minificado não tem os estilos das abas</p>";
    }
    
    if (strpos($css, 'comissao-container') !== false || strpos($css, 'comissao-section') !== false) {
        echo "✓ <span class='ok'>CSS da comissão encontrado</span><br>";
    } else {
        echo "✗ <span class='erro'>CSS da comissão NÃO encontrado!</span><br>";
        echo "<p><strong>PROBLEMA:</strong> O CSS minificado não tem os estilos da comissão</p>";
    }
    
    echo "<p>Tamanho do arquivo: " . round(filesize('assets/css/style.min.css')/1024, 2) . " KB</p>";
} else {
    echo "✗ <span class='erro'>style.min.css não encontrado!</span><br>";
}

echo "<h2>3. Verificando JavaScript (script.min.js)</h2>";
if (file_exists('assets/js/script.min.js')) {
    $js = file_get_contents('assets/js/script.min.js');
    
    if (strpos($js, 'tab-btn') !== false || strpos($js, 'profissional-tabs') !== false) {
        echo "✓ <span class='ok'>JavaScript das abas encontrado</span><br>";
    } else {
        echo "✗ <span class='erro'>JavaScript das abas NÃO encontrado!</span><br>";
        echo "<p><strong>PROBLEMA:</strong> O JS minificado não tem o código das abas</p>";
    }
    
    if (strpos($js, 'comissao') !== false) {
        echo "✓ <span class='ok'>JavaScript da comissão encontrado</span><br>";
    } else {
        echo "✗ <span class='erro'>JavaScript da comissão NÃO encontrado!</span><br>";
        echo "<p><strong>PROBLEMA:</strong> O JS minificado não tem o código da comissão</p>";
    }
    
    if (strpos($js, 'abrirModalComissao') !== false) {
        echo "✓ <span class='ok'>Função do modal da comissão encontrada</span><br>";
    } else {
        echo "✗ <span class='erro'>Função do modal NÃO encontrada!</span><br>";
    }
    
    echo "<p>Tamanho do arquivo: " . round(filesize('assets/js/script.min.js')/1024, 2) . " KB</p>";
} else {
    echo "✗ <span class='erro'>script.min.js não encontrado!</span><br>";
}

echo "<h2>4. Verificando admin/includes/sidebar.php</h2>";
if (file_exists('admin/includes/sidebar.php')) {
    $sidebar = file_get_contents('admin/includes/sidebar.php');
    
    if (strpos($sidebar, 'comissao.php') !== false) {
        echo "✓ <span class='ok'>Link para comissao.php encontrado</span><br>";
        
        // Mostrar trecho do código
        preg_match('/.*comissao\.php.*/', $sidebar, $matches);
        if ($matches) {
            echo "<details><summary>Ver código do link</summary><pre>" . htmlspecialchars($matches[0]) . "</pre></details>";
        }
    } else {
        echo "✗ <span class='erro'>Link para comissao.php NÃO encontrado!</span><br>";
        echo "<p><strong>PROBLEMA:</strong> O sidebar não tem o link para a comissão</p>";
    }
} else {
    echo "✗ <span class='erro'>sidebar.php não encontrado!</span><br>";
}

echo "<h2>5. RESUMO</h2>";
echo "<p><strong>Se houver algum ✗ vermelho acima, significa que esse arquivo precisa ser atualizado no servidor!</strong></p>";
echo "<p>Data do teste: " . date('Y-m-d H:i:s') . "</p>";
?>
