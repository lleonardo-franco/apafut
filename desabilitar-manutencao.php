<?php
/**
 * Script para desabilitar modo manutenção temporariamente
 * 
 * Este script renomeia o .htaccess para desabilitar o modo manutenção
 * Use com cuidado! Lembre-se de reativar depois dos testes.
 * 
 * Acesso: https://apafutoficial.com.br/desabilitar-manutencao.php?senha=apafut2025
 */

$senha_correta = 'apafut2025';
$senha = $_GET['senha'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Modo Manutenção - APAFUT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 { color: #1a202c; font-size: 28px; margin-bottom: 24px; text-align: center; }
        .status {
            background: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success { background: #c6f6d5; color: #22543d; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .error { background: #fed7d7; color: #742a2a; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .warning { background: #fef5e7; color: #7d6608; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin: 12px 0;
            text-decoration: none;
            text-align: center;
        }
        .btn-danger { background: #f56565; color: white; }
        .btn-success { background: #48bb78; color: white; }
        .btn-primary { background: #667eea; color: white; }
        .code {
            background: #2d3748;
            color: #68d391;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 12px 0;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Gerenciar Modo Manutenção</h1>
        
        <?php if ($senha !== $senha_correta): ?>
            <div class="error">
                <strong>❌ Acesso Negado</strong><br>
                Senha incorreta ou não fornecida.
            </div>
            <div class="code">
                https://apafutoficial.com.br/desabilitar-manutencao.php?senha=apafut2025
            </div>
        <?php else: ?>
            
            <?php
            $htaccess = __DIR__ . '/.htaccess';
            $htaccess_backup = __DIR__ . '/.htaccess.backup';
            $modo_ativo = file_exists($htaccess);
            
            $acao = $_GET['acao'] ?? '';
            
            if ($acao === 'desabilitar' && $modo_ativo) {
                if (rename($htaccess, $htaccess_backup)) {
                    echo '<div class="success"><strong>✅ Modo Manutenção DESABILITADO</strong><br>O .htaccess foi renomeado para .htaccess.backup</div>';
                    $modo_ativo = false;
                } else {
                    echo '<div class="error"><strong>❌ Erro ao desabilitar</strong><br>Não foi possível renomear o arquivo.</div>';
                }
            } elseif ($acao === 'habilitar' && !$modo_ativo) {
                if (rename($htaccess_backup, $htaccess)) {
                    echo '<div class="success"><strong>✅ Modo Manutenção HABILITADO</strong><br>O .htaccess foi restaurado.</div>';
                    $modo_ativo = true;
                } else {
                    echo '<div class="error"><strong>❌ Erro ao habilitar</strong><br>Não foi possível restaurar o arquivo.</div>';
                }
            }
            ?>
            
            <div class="status">
                <h2>Status Atual</h2>
                <?php if ($modo_ativo): ?>
                    <div class="warning">
                        <strong>⚠️ MODO MANUTENÇÃO ATIVO</strong><br>
                        O site está bloqueado para visitantes.
                    </div>
                    <a href="?senha=<?= $senha_correta ?>&acao=desabilitar" class="btn btn-danger">
                        🔓 Desabilitar Modo Manutenção
                    </a>
                <?php else: ?>
                    <div class="success">
                        <strong>✅ SITE PÚBLICO</strong><br>
                        O modo manutenção está desabilitado.
                    </div>
                    <a href="?senha=<?= $senha_correta ?>&acao=habilitar" class="btn btn-success">
                        🔒 Habilitar Modo Manutenção
                    </a>
                <?php endif; ?>
            </div>
            
            <a href="/" class="btn btn-primary">🏠 Ir para o Site</a>
            
            <div class="warning" style="margin-top: 24px;">
                <strong>⚠️ IMPORTANTE:</strong><br>
                • Não esqueça de reativar o modo manutenção depois dos testes<br>
                • Este arquivo deve ser removido em produção final<br>
                • Mantenha a senha segura
            </div>
            
        <?php endif; ?>
    </div>
</body>
</html>
