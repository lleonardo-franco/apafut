<?php
/**
 * Script de Teste - Preview Mode
 * Use este arquivo para diagnosticar problemas com o parâmetro preview
 * 
 * Acesso: https://apafutoficial.com.br/test-preview.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Preview Mode - APAFUT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #1a202c;
            font-size: 28px;
            margin-bottom: 24px;
            text-align: center;
        }
        .section {
            background: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section h2 {
            color: #2d3748;
            font-size: 18px;
            margin-bottom: 12px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #4a5568;
        }
        .value {
            color: #1a202c;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .success {
            background: #c6f6d5;
            color: #22543d;
        }
        .error {
            background: #fed7d7;
            color: #742a2a;
        }
        .instructions {
            background: #e6fffa;
            border-left: 4px solid #319795;
            padding: 16px;
            margin-top: 24px;
            border-radius: 4px;
        }
        .instructions h3 {
            color: #234e52;
            margin-bottom: 8px;
        }
        .instructions ol {
            margin-left: 20px;
            color: #2c7a7b;
        }
        .instructions li {
            margin: 6px 0;
        }
        .code {
            background: #2d3748;
            color: #68d391;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 12px 0;
            overflow-x: auto;
        }
        .warning {
            background: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 16px;
            margin-top: 16px;
            border-radius: 4px;
            color: #7d6608;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Preview Mode - APAFUT</h1>
        
        <div class="section">
            <h2>📊 Informações do Servidor</h2>
            <div class="info-row">
                <span class="label">Mod Rewrite:</span>
                <span class="value <?php echo in_array('mod_rewrite', apache_get_modules()) ? 'success' : 'error'; ?>">
                    <?php echo in_array('mod_rewrite', apache_get_modules()) ? '✓ Habilitado' : '✗ Desabilitado'; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Arquivo .htaccess:</span>
                <span class="value <?php echo file_exists(__DIR__ . '/.htaccess') ? 'success' : 'error'; ?>">
                    <?php echo file_exists(__DIR__ . '/.htaccess') ? '✓ Existe' : '✗ Não encontrado'; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Servidor:</span>
                <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido'; ?></span>
            </div>
            <div class="info-row">
                <span class="label">PHP Version:</span>
                <span class="value"><?php echo phpversion(); ?></span>
            </div>
        </div>

        <div class="section">
            <h2>🔗 Informações da Query String</h2>
            <div class="info-row">
                <span class="label">Query String Completa:</span>
                <span class="value"><?php echo !empty($_SERVER['QUERY_STRING']) ? htmlspecialchars($_SERVER['QUERY_STRING']) : '(vazia)'; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Parâmetro 'preview':</span>
                <span class="value <?php echo isset($_GET['preview']) ? 'success' : 'error'; ?>">
                    <?php 
                    if (isset($_GET['preview'])) {
                        echo '✓ Detectado: ' . htmlspecialchars($_GET['preview']);
                    } else {
                        echo '✗ Não encontrado';
                    }
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Preview Correto:</span>
                <span class="value <?php echo (isset($_GET['preview']) && $_GET['preview'] === 'apafut2025') ? 'success' : 'error'; ?>">
                    <?php 
                    if (isset($_GET['preview']) && $_GET['preview'] === 'apafut2025') {
                        echo '✓ Sim - preview=apafut2025';
                    } else {
                        echo '✗ Não corresponde';
                    }
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="label">URL Atual:</span>
                <span class="value" style="font-size: 12px;">
                    <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A'); ?>
                </span>
            </div>
        </div>

        <div class="section">
            <h2>🧪 Teste de Acesso</h2>
            <?php if (isset($_GET['preview']) && $_GET['preview'] === 'apafut2025'): ?>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value success">✓ Preview Mode ATIVO - Você pode acessar o site!</span>
                </div>
                <div class="code">
                    &lt;a href="/"&gt;Ir para o site&lt;/a&gt; ou 
                    &lt;a href="/?preview=apafut2025"&gt;Home com preview&lt;/a&gt;
                </div>
            <?php else: ?>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value error">✗ Preview Mode INATIVO</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="instructions">
            <h3>📋 Como Usar o Preview Mode:</h3>
            <ol>
                <li>Adicione <code>?preview=apafut2025</code> ao final de qualquer URL</li>
                <li>Exemplos:
                    <div class="code">
                        https://apafutoficial.com.br/?preview=apafut2025<br>
                        https://apafutoficial.com.br/index.php?preview=apafut2025<br>
                        https://apafutoficial.com.br/historia.html?preview=apafut2025
                    </div>
                </li>
                <li>Se o mod_rewrite estiver desabilitado acima, contate o suporte da Hostinger</li>
                <li>Se o .htaccess não existe, faça upload manual via FTP/File Manager</li>
            </ol>
        </div>

        <?php if (!file_exists(__DIR__ . '/.htaccess')): ?>
        <div class="warning">
            <strong>⚠️ ATENÇÃO:</strong> O arquivo .htaccess não foi encontrado no servidor!<br>
            Você precisa fazer upload do arquivo .htaccess para o diretório raiz (public_html).
        </div>
        <?php endif; ?>

        <?php if (!in_array('mod_rewrite', apache_get_modules())): ?>
        <div class="warning">
            <strong>⚠️ ATENÇÃO:</strong> O mod_rewrite não está habilitado!<br>
            Entre em contato com o suporte da Hostinger para habilitar o módulo mod_rewrite do Apache.
        </div>
        <?php endif; ?>

        <div class="instructions" style="background: #fff5f5; border-color: #fc8181;">
            <h3>🔧 Problemas Comuns:</h3>
            <ol>
                <li><strong>Ainda redireciona para manutenção:</strong> Limpe o cache do navegador (Ctrl+Shift+Delete)</li>
                <li><strong>Mod_rewrite desabilitado:</strong> Abra ticket no suporte Hostinger</li>
                <li><strong>.htaccess ausente:</strong> Faça upload via File Manager (Gerenciador de Arquivos)</li>
                <li><strong>Query string não funciona:</strong> Pode ser configuração do servidor, teste sem HTTPS primeiro</li>
            </ol>
        </div>
    </div>
</body>
</html>
