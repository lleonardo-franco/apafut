<?php
/**
 * Ativar Modo Manutenção
 * 
 * Acesso: https://apafutoficial.com.br/ativar-manutencao.php?senha=apafut2025admin
 */

require_once __DIR__ . '/includes/maintenance-check.php';

$senha_correta = 'apafut2025admin';
$senha = $_GET['senha'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $senha === $senha_correta) {
    if (file_put_contents(MAINTENANCE_FILE, json_encode([
        'ativado_em' => date('Y-m-d H:i:s'),
        'ativado_por' => $_SERVER['REMOTE_ADDR'],
        'motivo' => $_POST['motivo'] ?? 'Manutenção programada'
    ], JSON_PRETTY_PRINT))) {
        $success = true;
    } else {
        $error = 'Erro ao criar arquivo de manutenção';
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ativar Modo Manutenção - APAFUT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
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
        h1 { color: #742a2a; font-size: 28px; margin-bottom: 24px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #2d3748; }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
        }
        textarea { min-height: 100px; resize: vertical; }
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
        .btn-secondary { background: #718096; color: white; }
        .success { background: #c6f6d5; color: #22543d; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .error { background: #fed7d7; color: #742a2a; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .warning { background: #fef5e7; color: #7d6608; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .info { background: #bee3f8; color: #2c5282; padding: 16px; border-radius: 8px; margin: 16px 0; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Ativar Modo Manutenção</h1>
        
        <?php if (isset($success)): ?>
            <div class="success">
                <strong>✅ Modo Manutenção Ativado!</strong><br>
                O site agora exibe a página de manutenção para todos os visitantes.
            </div>
            <div class="info">
                <strong>💡 Para acessar o site durante a manutenção:</strong><br>
                Use este link: <strong><?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>?access=<?= $senha_correta ?></strong><br>
                O acesso será válido por 24 horas.
            </div>
            <a href="/" class="btn btn-secondary">🏠 Ir para o Site</a>
            <a href="/desativar-manutencao.php?senha=<?= $senha_correta ?>" class="btn btn-danger">🔓 Desativar Manutenção</a>
        <?php elseif (isset($error)): ?>
            <div class="error">
                <strong>❌ Erro:</strong> <?= $error ?>
            </div>
        <?php elseif ($senha !== $senha_correta): ?>
            <div class="error">
                <strong>❌ Acesso Negado</strong><br>
                Senha incorreta ou não fornecida.
            </div>
            <div class="info">
                URL correta: <strong>/ativar-manutencao.php?senha=apafut2025admin</strong>
            </div>
        <?php else: ?>
            <?php if (isMaintenanceMode()): ?>
                <div class="warning">
                    <strong>⚠️ O modo manutenção já está ativo!</strong><br>
                    Ativado em: <?= json_decode(file_get_contents(MAINTENANCE_FILE), true)['ativado_em'] ?? 'Desconhecido' ?>
                </div>
                <a href="/desativar-manutencao.php?senha=<?= $senha_correta ?>" class="btn btn-danger">🔓 Desativar Manutenção</a>
            <?php else: ?>
                <form method="POST" action="?senha=<?= $senha_correta ?>">
                    <div class="form-group">
                        <label>Motivo da Manutenção:</label>
                        <textarea name="motivo" placeholder="Ex: Atualização do sistema, correção de bugs, etc.">Manutenção programada</textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-danger">
                        🔒 Ativar Modo Manutenção
                    </button>
                </form>
                
                <div class="warning">
                    <strong>⚠️ ATENÇÃO:</strong><br>
                    • O site ficará inacessível para todos os visitantes<br>
                    • Você poderá acessar com o link especial que será fornecido<br>
                    • O painel admin permanecerá acessível
                </div>
            <?php endif; ?>
            
            <a href="/" class="btn btn-secondary">🏠 Voltar ao Site</a>
        <?php endif; ?>
    </div>
</body>
</html>
