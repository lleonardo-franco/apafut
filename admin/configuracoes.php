<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once 'auth.php';

$erro = '';
$sucesso = '';
$pdo = getConnection();

// Buscar configurações atuais (usuário logado)
$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $senha_nova = $_POST['senha_nova'] ?? '';
        $senha_confirmar = $_POST['senha_confirmar'] ?? '';
        
        // Validação
        if (empty($senha_atual) || empty($senha_nova) || empty($senha_confirmar)) {
            $erro = 'Todos os campos de senha são obrigatórios';
        } elseif ($senha_nova !== $senha_confirmar) {
            $erro = 'A nova senha e a confirmação não coincidem';
        } elseif (strlen($senha_nova) < 6) {
            $erro = 'A nova senha deve ter pelo menos 6 caracteres';
        } else {
            // Verificar senha atual
            $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);
            $usuario = $stmt->fetch();
            
            if (!password_verify($senha_atual, $usuario['senha'])) {
                $erro = 'Senha atual incorreta';
            } else {
                // Atualizar senha
                $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
                $stmt->execute([
                    ':senha' => $senha_hash,
                    ':id' => $user['id']
                ]);
                
                $sucesso = 'Senha alterada com sucesso!';
            }
        }
    } elseif ($acao === 'alterar_nome') {
        $nome = trim($_POST['nome'] ?? '');
        
        if (empty($nome)) {
            $erro = 'O nome é obrigatório';
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome WHERE id = :id");
            $stmt->execute([
                ':nome' => $nome,
                ':id' => $user['id']
            ]);
            
            // Atualizar sessão
            $_SESSION['user']['nome'] = $nome;
            $user['nome'] = $nome;
            
            $sucesso = 'Nome atualizado com sucesso!';
        }
    }
}

// Estatísticas do sistema
$stats = [];
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM noticias");
    $stats['noticias'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jogadores");
    $stats['jogadores'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM depoimentos");
    $stats['depoimentos'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM planos");
    $stats['planos'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $stats['usuarios'] = $stmt->fetch()['total'];
} catch (Exception $e) {
    // Ignorar erros de estatísticas
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Painel Administrativo</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }
        
        .config-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .config-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .config-card p {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }
        
        .config-card .form-group {
            margin-bottom: 16px;
        }
        
        .config-card .form-group:last-child {
            margin-bottom: 0;
        }
        
        .config-card label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        
        .config-card input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .config-card input:focus {
            outline: none;
            border-color: var(--vermelho-primario);
            box-shadow: 0 0 0 3px rgba(235, 56, 53, 0.1);
        }
        
        .config-card .btn {
            width: 100%;
            justify-content: center;
            margin-top: 16px;
        }
        
        .stats-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .stat-item-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }
        
        .stat-item-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--vermelho-primario);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc 0%, white 100%);
            border-radius: 12px;
            margin-bottom: 24px;
        }
        
        .user-profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--vermelho-primario), #d32f2f);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        
        .user-profile-info h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 4px 0;
        }
        
        .user-profile-info p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }
        
        .user-profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content">
                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="header-text">
                            <h1>Configurações</h1>
                            <p>Gerencie as configurações do sistema e sua conta</p>
                        </div>
                    </div>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($sucesso) ?>
                    </div>
                <?php endif; ?>

                <div class="user-profile">
                    <div class="user-profile-avatar">
                        <?= Auth::getInitials($user['nome']) ?>
                    </div>
                    <div class="user-profile-info">
                        <h2><?= htmlspecialchars($user['nome']) ?></h2>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                        <span class="user-profile-badge">
                            <i class="fas fa-shield-alt"></i>
                            <?= ucfirst($user['nivel_acesso']) ?>
                        </span>
                    </div>
                </div>

                <div class="config-grid">
                    <!-- Alterar Nome -->
                    <div class="config-card">
                        <h3><i class="fas fa-user"></i> Alterar Nome</h3>
                        <p>Atualize o nome exibido no sistema</p>
                        <form method="POST">
                            <input type="hidden" name="acao" value="alterar_nome">
                            <div class="form-group">
                                <label for="nome">Nome Completo</label>
                                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($user['nome']) ?>">
                            </div>
                            <button type="submit" class="btn-balanced">
                                <i class="fas fa-check"></i> Salvar Nome
                            </button>
                        </form>
                    </div>

                    <!-- Alterar Senha -->
                    <div class="config-card">
                        <h3><i class="fas fa-lock"></i> Alterar Senha</h3>
                        <p>Mantenha sua conta segura alterando a senha regularmente</p>
                        <form method="POST">
                            <input type="hidden" name="acao" value="alterar_senha">
                            <div class="form-group">
                                <label for="senha_atual">Senha Atual</label>
                                <input type="password" id="senha_atual" name="senha_atual">
                            </div>
                            <div class="form-group">
                                <label for="senha_nova">Nova Senha</label>
                                <input type="password" id="senha_nova" name="senha_nova" minlength="6">
                                <small style="display: block; margin-top: 4px; color: #666;">Mínimo de 6 caracteres</small>
                            </div>
                            <div class="form-group">
                                <label for="senha_confirmar">Confirmar Nova Senha</label>
                                <input type="password" id="senha_confirmar" name="senha_confirmar" minlength="6">
                            </div>
                            <button type="submit" class="btn-balanced">
                                <i class="fas fa-key"></i> Alterar Senha
                            </button>
                        </form>
                    </div>

                    <!-- Estatísticas do Sistema -->
                    <div class="config-card">
                        <h3><i class="fas fa-chart-bar"></i> Estatísticas do Sistema</h3>
                        <p>Resumo dos dados cadastrados no sistema</p>
                        <div class="stats-info">
                            <div class="stat-item">
                                <span class="stat-item-label">Notícias</span>
                                <span class="stat-item-value"><?= $stats['noticias'] ?? 0 ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-item-label">Jogadores</span>
                                <span class="stat-item-value"><?= $stats['jogadores'] ?? 0 ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-item-label">Depoimentos</span>
                                <span class="stat-item-value"><?= $stats['depoimentos'] ?? 0 ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-item-label">Planos</span>
                                <span class="stat-item-value"><?= $stats['planos'] ?? 0 ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-item-label">Usuários</span>
                                <span class="stat-item-value"><?= $stats['usuarios'] ?? 0 ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-item-label">PHP</span>
                                <span class="stat-item-value" style="font-size: 14px;"><?= PHP_VERSION ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
