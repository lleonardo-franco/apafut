<!DOCTYPE html>
<html>
<head>
    <title>Atualizando Favicon nos Admin</title>
</head>
<body>
    <h1>Atualizando Favicon - Caminho Absoluto</h1>
    <?php
    $adminDir = __DIR__ . '/admin';
    $files = glob($adminDir . '/*.php');
    
    $updated = 0;
    $skipped = 0;
    
    // Padrão antigo (caminho relativo)
    $oldPattern1 = '<link rel="icon" type="image/x-icon" href="../assets/logo.ico">';
    $oldPattern2 = '<link rel="shortcut icon" type="image/x-icon" href="../assets/logo.ico">';
    $oldPattern3 = '<link rel="apple-touch-icon" href="../assets/logo.png">';
    
    // Novo padrão (caminho absoluto)
    $newFavicon = '<link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">';
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $basename = basename($file);
        
        // Remover tags antigas se existirem
        $content = str_replace($oldPattern1, '', $content);
        $content = str_replace($oldPattern2, '', $content);
        $content = str_replace($oldPattern3, '', $content);
        
        // Adicionar novas tags após o <title>
        if (preg_match('/<title>.*?<\/title>/i', $content, $matches)) {
            $newContent = str_replace($matches[0], $matches[0] . "\n    " . $newFavicon, $content);
            
            if ($newContent !== $content) {
                file_put_contents($file, $newContent);
                echo "✅ Atualizado: $basename<br>\n";
                $updated++;
            } else {
                echo "⚠️  Não modificado: $basename<br>\n";
                $skipped++;
            }
        }
    }
    
    // Também atualizar index.php
    $indexFile = __DIR__ . '/index.php';
    if (file_exists($indexFile)) {
        $content = file_get_contents($indexFile);
        
        // Padrões antigos no index
        $oldIndex1 = '<link rel="icon" type="image/x-icon" href="assets/logo.ico">';
        $oldIndex2 = '<link rel="shortcut icon" type="image/x-icon" href="assets/logo.ico">';
        $oldIndex3 = '<link rel="apple-touch-icon" href="assets/logo.png">';
        
        $newIndexFavicon = '<link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">';
        
        $content = str_replace($oldIndex1, '', $content);
        $content = str_replace($oldIndex2, '', $content);
        $content = str_replace($oldIndex3, '', $content);
        
        if (preg_match('/<title>.*?<\/title>/i', $content, $matches)) {
            $newContent = str_replace($matches[0], $matches[0] . "\n    " . $newIndexFavicon, $content);
            file_put_contents($indexFile, $newContent);
            echo "✅ Atualizado: index.php<br>\n";
            $updated++;
        }
    }
    
    echo "<hr>";
    echo "<h2>Resumo:</h2>";
    echo "✅ Arquivos atualizados: $updated<br>";
    echo "⚠️  Arquivos não modificados: $skipped<br>";
    echo "<p><strong>ATENÇÃO:</strong> Limpe o cache do navegador (Ctrl+F5) para ver as mudanças!</p>";
    ?>
</body>
</html>
