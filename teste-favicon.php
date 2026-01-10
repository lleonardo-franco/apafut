<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Favicon - APAFUT</title>
    <link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .test-box {
            border: 2px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        img {
            max-width: 100px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <h1>🔍 Teste de Favicon - APAFUT</h1>
    
    <div class="test-box">
        <h2>1. Verificação Visual do Logo</h2>
        <p>Se você conseguir ver as imagens abaixo, os arquivos existem e são válidos:</p>
        
        <div>
            <strong>logo.ico (16x16):</strong><br>
            <img src="/apafut/assets/logo.ico" alt="Logo ICO" style="border: 1px solid #000;">
        </div>
        
        <div>
            <strong>logo.png (original):</strong><br>
            <img src="/apafut/assets/logo.png" alt="Logo PNG" style="border: 1px solid #000;">
        </div>
    </div>
    
    <div class="test-box">
        <h2>2. Instruções de Teste</h2>
        <ol>
            <li>Olhe na aba do navegador - você deve ver o logo da APAFUT</li>
            <li>Se ainda aparecer o ícone de mundo, pressione <strong>Ctrl + F5</strong> para limpar o cache</li>
            <li>Se continuar com o ícone de mundo, o arquivo logo.ico pode estar corrompido</li>
        </ol>
    </div>
    
    <div class="test-box">
        <h2>3. Informações Técnicas</h2>
        <?php
        $logoIco = __DIR__ . '/assets/logo.ico';
        $logoPng = __DIR__ . '/assets/logo.png';
        
        echo "<p><strong>logo.ico:</strong> ";
        if (file_exists($logoIco)) {
            $size = filesize($logoIco);
            echo "✅ Existe ($size bytes)";
            
            // Verificar se é um arquivo ICO válido
            $handle = fopen($logoIco, 'rb');
            $header = fread($handle, 6);
            fclose($handle);
            
            $headerData = unpack('vreserved/vtype/vcount', $header);
            
            if ($headerData['type'] === 1) {
                echo " - ✅ Formato ICO válido";
            } else {
                echo " - ⚠️ ATENÇÃO: Não parece ser um arquivo ICO válido!";
            }
        } else {
            echo "❌ Não existe";
        }
        echo "</p>";
        
        echo "<p><strong>logo.png:</strong> ";
        if (file_exists($logoPng)) {
            $size = filesize($logoPng);
            echo "✅ Existe (" . number_format($size) . " bytes)";
        } else {
            echo "❌ Não existe";
        }
        echo "</p>";
        ?>
    </div>
    
    <div class="test-box">
        <h2>4. Próximos Passos</h2>
        <p>Se o favicon ainda não aparecer depois de limpar o cache:</p>
        <ul>
            <li>O arquivo logo.ico pode precisar ser recriado</li>
            <li>Use um conversor online como <strong>https://favicon.io/</strong></li>
            <li>Faça upload do logo.png e baixe o favicon.ico gerado</li>
            <li>Substitua o arquivo em assets/logo.ico</li>
        </ul>
    </div>
    
</body>
</html>
