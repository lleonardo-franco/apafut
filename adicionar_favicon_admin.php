<?php
// Script para adicionar favicon em todos os arquivos PHP do admin

$adminDir = __DIR__ . '/admin';
$files = glob($adminDir . '/*.php');

$faviconCode = '    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/logo.png">';

$count = 0;
$updated = [];
$skipped = [];

foreach ($files as $file) {
    $filename = basename($file);
    
    // Pular alguns arquivos específicos
    if (in_array($filename, ['auth.php', 'logout.php', 'verificar-sidebar.php', 'teste-sidebar.php'])) {
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Verificar se já tem favicon
    if (strpos($content, 'favicon') !== false || strpos($content, 'logo.ico') !== false) {
        $skipped[] = $filename;
        continue;
    }
    
    // Procurar pela tag <title> e adicionar o favicon logo após
    if (preg_match('/<title>.*?<\/title>/s', $content, $matches)) {
        $newContent = str_replace($matches[0], $matches[0] . "\n" . $faviconCode, $content);
        
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            $updated[] = $filename;
            $count++;
        }
    }
}

echo "=== ADIÇÃO DE FAVICON NO ADMIN ===\n\n";
echo "Arquivos atualizados: " . $count . "\n\n";

if (!empty($updated)) {
    echo "✅ Arquivos com favicon adicionado:\n";
    foreach ($updated as $file) {
        echo "  - " . $file . "\n";
    }
}

if (!empty($skipped)) {
    echo "\n⏭️  Arquivos pulados (já tinham favicon ou são especiais):\n";
    foreach ($skipped as $file) {
        echo "  - " . $file . "\n";
    }
}

echo "\n✅ Concluído!\n";
