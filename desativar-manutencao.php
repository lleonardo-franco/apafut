<?php
/**
 * Desativar Modo Manutenção
 * 
 * Acesso: https://apafutoficial.com.br/desativar-manutencao.php?senha=apafut2025admin
 */

require_once __DIR__ . '/includes/maintenance-check.php';

$senha_correta = 'apafut2025admin';
$senha = $_GET['senha'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $senha === $senha_correta) {
    if (file_exists(MAINTENANCE_FILE)) {
        if (unlink(MAINTENANCE_FILE)) {
            $success = true;
        } else {
            $error = 'Erro ao remover arquivo de manutenção';
        }
    } else {
        $error = 'Modo manutenção já está desativado';
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desativar Modo Manutenção - APAFUT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
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
        h1 { color: #22543d; font-size: 28px; margin-bottom: 24px; text-align: center; }
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
        .btn-success { background: #48bb78; color: white; }
        .btn-secondary { background: #718096; color: white; }
        .success { background: #c6f6d5; color: #22543d; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .error { background: #fed7d7; color: #742a2a; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .warning { background: #fef5e7; color: #7d6608; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .info { background: #e6fffa; color: #234e52; padding: 16px; border-radius: 8px; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔓 Desativar Modo Manutenção</h1>
        
        <?php if (isset($success)): ?>
            <div class="success">
                <strong>✅ Modo Manutenção Desativado!</strong><br>
                O site está novamente acessível para todos os visitantes.
            </div>
            <a href="/" class="btn btn-success">🏠 Ir para o Site</a>
        <?php elseif (isset($error)): ?>
            <div class="error">
                <strong>❌ Erro:</strong> <?= $error ?>
            </div>
            <a href="/" class="btn btn-secondary">🏠 Voltar ao Site</a>
        <?php elseif ($senha !== $senha_correta): ?>
            <div class="error">
                <strong>❌ Acesso Negado</strong><br>
                Senha incorreta ou não fornecida.
            </div>
            <div class="info">
                URL correta: <strong>/desativar-manutencao.php?senha=apafut2025admin</strong>
            </div>
        <?php else: ?>
            <?php if (isMaintenanceMode()): ?>
                <div class="info">
                    <strong>📊 Status do Modo Manutenção:</strong><br>
                    <?php 
                    $maintenance_data = json_decode(file_get_contents(MAINTENANCE_FILE), true);
                    echo "Ativado em: " . ($maintenance_data['ativado_em'] ?? 'Desconhecido') . "<br>";
                    echo "Motivo: " . ($maintenance_data['motivo'] ?? 'Não especificado');
                    ?>
                </div>
                
                <form method="POST" action="?senha=<?= $senha_correta ?>">
                    <button type="submit" class="btn btn-success">
                        🔓 Desativar Modo Manutenção
                    </button>
                </form>
                
                <div class="warning">
                    <strong>⚠️ O site voltará a ficar acessível para todos após a desativação.</strong>
                </div>
            <?php else: ?>
                <div class="warning">
                    <strong>⚠️ O modo manutenção já está desativado!</strong><br>
                    O site está acessível normalmente.
                </div>
                <a href="/ativar-manutencao.php?senha=<?= $senha_correta ?>" class="btn btn-secondary">🔒 Ativar Manutenção</a>
            <?php endif; ?>
            
            <a href="/" class="btn btn-secondary">🏠 Voltar ao Site</a>
        <?php endif; ?>
    </div>
</body>
</html>
